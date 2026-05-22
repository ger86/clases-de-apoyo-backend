<?php

namespace App\Service\Stripe;

use App\Service\Security;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeCreateCheckoutSession
{
    public const PLAN_MONTHLY = 'monthly';
    public const PLAN_YEARLY = 'yearly';

    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey,
        #[Autowire('%app.stripe.monthly_price_id%')]
        private string $monthlyPriceId,
        #[Autowire('%app.stripe.yearly_price_id%')]
        private string $yearlyPriceId,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function __invoke(string $plan): Session
    {
        $currentUser = $this->security->getSafeUser();
        $priceId = match ($plan) {
            self::PLAN_MONTHLY => $this->monthlyPriceId,
            self::PLAN_YEARLY => $this->yearlyPriceId,
            default => throw new \InvalidArgumentException(\sprintf('Unknown subscription plan "%s".', $plan)),
        };

        Stripe::setApiKey($this->secretKey);

        return Session::create([
            'success_url' => $this->urlGenerator->generate('app_subscription_payment_success', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_subscription_payment', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'client_reference_id' => $currentUser->getId(),
            'customer_email' => $currentUser->getEmail(),
            'metadata' => [
                'subscription_plan' => $plan,
            ],
        ]);
    }
}
