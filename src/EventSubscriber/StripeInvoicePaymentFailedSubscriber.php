<?php

namespace App\EventSubscriber;

use App\Event\StripeInvoicePaymentFailedEvent;
use App\Model\Dto\StripeProcessInvoicePaymentFailedArguments;
use App\Repository\UserRepository;
use App\Service\Stripe\StripeProcessInvoicePaymentFailed;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeInvoicePaymentFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private StripeProcessInvoicePaymentFailed $processInvoicePaymentFailed
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StripeInvoicePaymentFailedEvent::class => [
                ['onFailedPayment', 10]
            ]
        ];
    }

    public function onFailedPayment(StripeInvoicePaymentFailedEvent $event)
    {
        $invoice = $event->invoice;
        $user = $this->userRepository->findOneBy(['email' => $invoice->customer_email]);
        if ($user === null) {
            throw new Exception("User with email {$invoice->customer_email} not found");
        }
        $amount = (float)$invoice->amount_due / 100;
        $dto = new StripeProcessInvoicePaymentFailedArguments($user, $amount);
        ($this->processInvoicePaymentFailed)($dto);
    }
}
