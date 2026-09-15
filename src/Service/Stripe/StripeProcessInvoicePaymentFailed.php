<?php

namespace App\Service\Stripe;

use App\Event\SendMailEvent;
use App\Model\Dto\MailDto;
use App\Model\Dto\StripeProcessInvoicePaymentFailedArguments;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class StripeProcessInvoicePaymentFailed
{

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
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
        $this->dispatchMail($mailerDto);

        $mailerDto = new MailDto(
            'email/recurring_payment_failed_admin.html.twig',
            [
                'amount' => $arguments->dueAmount,
                'user' => $user
            ],
            'Se ha producido un error al recibir el pago de Clases de Apoyo Premium',
            $this->adminMail
        );
        $this->dispatchMail($mailerDto);
    }

    private function dispatchMail(MailDto $mailerDto): void
    {
        try {
            $this->eventDispatcher->dispatch(new SendMailEvent($mailerDto));
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Unable to send Stripe failed-payment email.', [
                'recipient' => $mailerDto->to,
                'exception' => $exception,
            ]);
        }
    }
}
