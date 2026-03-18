<?php

namespace App\Service\Stripe;

use App\Service\Security;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeCreateCheckoutSession
{

    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey,
        #[Autowire('%app.stripe.price_id%')]
        private string $priceId,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function __invoke(): Session
    {
        $currentUser = $this->security->getSafeUser();
        Stripe::setApiKey($this->secretKey);

        return Session::create([
            'success_url' => $this->urlGenerator->generate('app_subscription_payment_success', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_subscription_payment', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $this->priceId,
                'quantity' => 1,
            ]],
            'client_reference_id' => $currentUser->getId(),
            'customer_email' => $currentUser->getEmail()
        ]);
    }
}
