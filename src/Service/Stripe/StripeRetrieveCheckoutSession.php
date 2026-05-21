<?php

namespace App\Service\Stripe;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class StripeRetrieveCheckoutSession
{
    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey
    ) {
    }

    public function __invoke(string $sessionId): Session
    {
        Stripe::setApiKey($this->secretKey);

        return Session::retrieve($sessionId);
    }
}
