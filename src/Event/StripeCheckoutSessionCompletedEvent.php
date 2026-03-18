<?php

namespace App\Event;

use Stripe\Checkout\Session;
use Symfony\Contracts\EventDispatcher\Event;

class StripeCheckoutSessionCompletedEvent extends Event implements StripeEventInterface
{
    public function __construct(public readonly Session $session)
    {
    }
}
