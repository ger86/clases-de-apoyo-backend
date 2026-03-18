<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Stripe\Subscription;

class StripeCustomerSubscriptionUpdatedEvent extends Event implements StripeEventInterface
{
    public function __construct(public readonly Subscription $subscription)
    {
    }
}
