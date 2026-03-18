<?php

namespace App\Service\Stripe;

use Stripe\Event;
use App\Enum\StripeConsts;
use App\Event\StripeCheckoutSessionCompletedEvent;
use App\Event\StripeCustomerSubscriptionUpdatedEvent;
use App\Event\StripeEventInterface;
use App\Event\StripeInvoicePaymentSucceededEvent;
use App\Event\StripeInvoicePaymentFailedEvent;

final class StripeEventFactory
{

    public function __invoke(Event $stripeEvent): ?StripeEventInterface
    {
        $event = null;
        $type = $stripeEvent->type;
        $object = $stripeEvent->data->object;
        if (StripeConsts::INVOICE_PAYMENT_SUCCEEDED === $type) {
            $event = new StripeInvoicePaymentSucceededEvent($object);
        } else if (StripeConsts::INVOICE_PAYMENT_FAILED === $type) {
            $event = new StripeInvoicePaymentFailedEvent($object);
        } else if (StripeConsts::CHECKOUT_SESSION_COMPLETED === $type) {
            $event = new StripeCheckoutSessionCompletedEvent($object);
        } else if (StripeConsts::CUSTOMER_SUBSCRIPTION_UPDATED === $type) {
            $event = new StripeCustomerSubscriptionUpdatedEvent($object);
        }
        return $event;
    }
}
