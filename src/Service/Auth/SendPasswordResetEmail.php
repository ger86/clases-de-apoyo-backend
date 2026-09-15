<?php

namespace App\Service\Auth;

use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Sends the reset-password email. Returns null when nothing was sent, without
 * saying why, so neither the website nor the app can be used to discover
 * which email addresses have an account.
 */
final class SendPasswordResetEmail
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer
    ) {
    }

    public function __invoke(string $email): ?ResetPasswordToken
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user === null) {
            return null;
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return null;
        }

        $this->mailer->send(
            (new TemplatedEmail())
                ->from(new Address('info@clasesdeapoyo.com', 'Clases de Apoyo'))
                ->to($user->getEmail())
                ->subject('Recupera tu contraseña en Clases de Apoyo')
                ->htmlTemplate('views/reset_password/email.html.twig')
                ->context(['resetToken' => $resetToken])
        );

        return $resetToken;
    }
}
