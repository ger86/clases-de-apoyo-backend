<?php

namespace App\Service\Stripe;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class StripeProcessWebhook
{

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private GetEventFromWebhook $getEventFromWebhook,
        private StripeEventFactory $stripeEventFactory
    ) {
    }

    public function __invoke(): void
    {
        $stripeEvent = ($this->getEventFromWebhook)();
        $eventToDispatch = ($this->stripeEventFactory)($stripeEvent);
        if ($eventToDispatch !== null) {
            $this->eventDispatcher->dispatch($eventToDispatch);
        }
    }
}
