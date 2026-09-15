<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

/**
 * Creates an account and sends the confirmation email.
 * Shared by the website registration form and the mobile app.
 */
final class RegisterUser
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailVerifier $emailVerifier,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(User $user, string $plainPassword): User
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        // The account already exists at this point, so a mail server problem must not
        // turn a successful sign-up into an error. It is logged and the user can ask for
        // a new confirmation email later.
        try {
            $this->sendConfirmationEmail($user);
        } catch (Throwable $exception) {
            $this->logger->error('Confirmation email could not be sent', [
                'userId' => $user->getId(),
                'exception' => $exception->getMessage(),
            ]);
        }

        return $user;
    }

    public function sendConfirmationEmail(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('info@clasesdeapoyo.com', 'Clases de Apoyo'))
                ->to($user->getEmail())
                ->subject('Por favor, confirma tu email')
                ->htmlTemplate('views/registration/confirmation_email.html.twig')
        );
    }
}
