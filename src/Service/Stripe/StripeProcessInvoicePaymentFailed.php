<?php

namespace App\Service\Stripe;

use App\Event\SendMailEvent;
use App\Model\Dto\MailDto;
use App\Model\Dto\StripeProcessInvoicePaymentFailedArguments;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class StripeProcessInvoicePaymentFailed
{

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        #[Autowire('%app.mail.default_from%')]
        private string $adminMail
    ) {
    }

    public function __invoke(StripeProcessInvoicePaymentFailedArguments $arguments): void
    {
        $user = $arguments->user;

        $mailerDto = new MailDto(
            'email/recurring_payment_failed.html.twig',
            [
                'amount' => $arguments->dueAmount,
                'user' => $user,
            ],
            'Se ha producido un error con el pago de Clases de Apoyo - Premium',
            $user->getEmail()
        );
        $event = new SendMailEvent($mailerDto);
        $this->eventDispatcher->dispatch($event);

        $mailerDto = new MailDto(
            'email/recurring_payment_failed_admin.html.twig',
            [
                'amount' => $arguments->dueAmount,
                'user' => $user
            ],
            'Se ha producido un error al recibir el pago de Clases de Apoyo Premium',
            $this->adminMail
        );
        $event = new SendMailEvent($mailerDto);
        $this->eventDispatcher->dispatch($event);
    }
}
