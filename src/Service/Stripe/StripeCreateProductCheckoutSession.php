<?php

namespace App\Service\Stripe;

use App\Entity\ProductPurchase;
use App\Service\Security;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeCreateProductCheckoutSession
{
    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function __invoke(ProductPurchase $purchase): Session
    {
        Stripe::setApiKey($this->secretKey);

        $user = $this->security->getUser();
        $product = $purchase->getProduct();

        $successUrl = $this->urlGenerator->generate('product_purchase_success', [
            'token' => $purchase->getDownloadToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $sessionPayload = [
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->urlGenerator->generate('product_show', [
                'slug' => $product->getSlug(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'mode' => 'payment',
            'line_items' => [[
                'price' => $product->getStripePriceId(),
                'quantity' => 1,
            ]],
            'client_reference_id' => (string) $purchase->getId(),
            'metadata' => [
                'purchase_id' => (string) $purchase->getId(),
                'product_code' => $product->getCode(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'purchase_id' => (string) $purchase->getId(),
                    'product_code' => $product->getCode(),
                ],
            ],
        ];

        if ($user !== null) {
            $sessionPayload['customer_email'] = $user->getEmail();
        }

        return Session::create($sessionPayload);
    }
}
