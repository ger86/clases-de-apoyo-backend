<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Model\View\ApiErrorView;
use App\Model\View\AuthSessionView;
use App\Repository\UserRepository;
use App\Service\Auth\ApiTokenManager;
use App\Service\Auth\RegisterUser;
use App\Service\Auth\SendPasswordResetEmail;
use App\Service\GetMeView;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class ApiAuthController extends AbstractFOSRestController
{
    private const MIN_PASSWORD_LENGTH = 6;

    public function __construct(
        private ApiTokenManager $apiTokenManager,
        private GetMeView $getMeView
    ) {
    }

    #[Post(path: '/auth/register')]
    public function registerAction(
        Request $request,
        UserRepository $userRepository,
        RegisterUser $registerUser,
        #[Autowire(service: 'limiter.api_register')]
        RateLimiterFactoryInterface $apiRegisterLimiter
    ): View {
        if (!$apiRegisterLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            return $this->tooManyRequests();
        }

        $payload = $request->getPayload();
        $email = mb_strtolower(trim((string) $payload->get('email')));
        $password = (string) $payload->get('password');

        $invalid = $this->validateCredentials($email, $password);
        if ($invalid !== null) {
            return $invalid;
        }

        if ($userRepository->findOneBy(['email' => $email]) !== null) {
            return $this->view(
                new ApiErrorView('Ya existe una cuenta con ese email.', 'email_already_used'),
                Response::HTTP_CONFLICT
            );
        }

        $user = ($registerUser)((new User())->setEmail($email), $password);

        return $this->view($this->createSession($user, $request), Response::HTTP_CREATED);
    }

    #[Post(path: '/auth/login')]
    public function loginAction(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        #[Autowire(service: 'limiter.api_login')]
        RateLimiterFactoryInterface $apiLoginLimiter
    ): View {
        if (!$apiLoginLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            return $this->tooManyRequests();
        }

        $payload = $request->getPayload();
        $email = mb_strtolower(trim((string) $payload->get('email')));
        $password = (string) $payload->get('password');

        $user = $userRepository->findOneBy(['email' => $email]);
        if (
            $user === null
            || $user->getPassword() === null
            || !$userPasswordHasher->isPasswordValid($user, $password)
        ) {
            return $this->view(
                new ApiErrorView('El email o la contraseña no son correctos.', 'invalid_credentials'),
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->view($this->createSession($user, $request));
    }

    #[Post(path: '/auth/logout')]
    public function logoutAction(Request $request): View
    {
        $header = (string) $request->headers->get('Authorization');
        if (str_starts_with($header, 'Bearer ')) {
            $this->apiTokenManager->revokeByPlainToken(trim(substr($header, 7)));
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    #[Post(path: '/auth/password-reset')]
    public function passwordResetAction(
        Request $request,
        SendPasswordResetEmail $sendPasswordResetEmail,
        #[Autowire(service: 'limiter.api_password_reset')]
        RateLimiterFactoryInterface $apiPasswordResetLimiter
    ): View {
        if (!$apiPasswordResetLimiter->create($request->getClientIp())->consume()->isAccepted()) {
            return $this->tooManyRequests();
        }

        $email = mb_strtolower(trim((string) $request->getPayload()->get('email')));
        if ($email !== '') {
            ($sendPasswordResetEmail)($email);
        }

        // Always the same answer, so the endpoint cannot be used to find out which emails have an account.
        return $this->view(null, Response::HTTP_ACCEPTED);
    }

    private function createSession(User $user, Request $request): AuthSessionView
    {
        $deviceName = $request->headers->get('X-Device-Name');
        $token = $this->apiTokenManager->issue($user, $deviceName === null ? null : mb_substr($deviceName, 0, 120));

        return new AuthSessionView($token, ($this->getMeView)($user));
    }

    private function validateCredentials(string $email, string $password): ?View
    {
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->view(
                new ApiErrorView('Por favor, introduce un email válido.', 'invalid_email'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return $this->view(
                new ApiErrorView(
                    \sprintf('Tu contraseña debe tener al menos %d caracteres.', self::MIN_PASSWORD_LENGTH),
                    'invalid_password'
                ),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return null;
    }

    private function tooManyRequests(): View
    {
        return $this->view(
            new ApiErrorView('Demasiados intentos. Inténtalo de nuevo más tarde.', 'too_many_requests'),
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }
}
