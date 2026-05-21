<?php

namespace App\EventSubscriber;

use App\Event\StripeCheckoutSessionCompletedEvent;
use App\Service\Product\CompleteProductPurchaseFromStripeSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeProductCheckoutSessionCompletedSubscriber implements EventSubscriberInterface
{
    public function __construct(private CompleteProductPurchaseFromStripeSession $completePurchase)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StripeCheckoutSessionCompletedEvent::class => [
                ['onCheckoutSessionCompleted', 20],
            ],
        ];
    }

    public function onCheckoutSessionCompleted(StripeCheckoutSessionCompletedEvent $event): void
    {
        ($this->completePurchase)($event->session);
    }
}
