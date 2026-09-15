<?php

namespace App\Security;

use App\Service\Auth\ApiTokenManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class ApiTokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private const HEADER = 'Authorization';
    private const PREFIX = 'Bearer ';

    public function __construct(private ApiTokenManager $apiTokenManager)
    {
    }

    /**
     * Requests without the header stay anonymous, so free content is still readable
     * without an account, exactly like the website.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER);
    }

    public function authenticate(Request $request): Passport
    {
        $header = (string) $request->headers->get(self::HEADER);
        if (!str_starts_with($header, self::PREFIX)) {
            throw new CustomUserMessageAuthenticationException('El formato del token no es válido.');
        }

        $plainToken = trim(substr($header, \strlen(self::PREFIX)));
        if ($plainToken === '') {
            throw new CustomUserMessageAuthenticationException('El token está vacío.');
        }

        $apiToken = $this->apiTokenManager->findValid($plainToken);
        if ($apiToken === null) {
            throw new CustomUserMessageAuthenticationException('La sesión ha caducado. Vuelve a iniciar sesión.');
        }

        $user = $apiToken->getUser();

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), static fn (): object => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['message' => $exception->getMessageKey(), 'code' => 'invalid_token'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            ['message' => 'Necesitas iniciar sesión.', 'code' => 'authentication_required'],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
