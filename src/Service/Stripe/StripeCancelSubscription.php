<?php

namespace App\Service\Stripe;

use Stripe\Stripe;
use Stripe\Subscription;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class StripeCancelSubscription
{
    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey
    ) {
    }

    public function __invoke(string $subscriptionId): void
    {
        Stripe::setApiKey($this->secretKey);
        Subscription::retrieve($subscriptionId)->cancel();
    }
}
