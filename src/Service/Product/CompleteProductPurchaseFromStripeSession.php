<?php

namespace App\Service\Product;

use App\Entity\ProductPurchase;
use App\Repository\ProductPurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;

final class CompleteProductPurchaseFromStripeSession
{
    public function __construct(
        private ProductPurchaseRepository $purchaseRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Session $session): void
    {
        if ($session->mode !== 'payment') {
            return;
        }

        $metadata = $session->metadata?->toArray() ?? [];
        $purchaseId = $metadata['purchase_id'] ?? $session->client_reference_id ?? null;
        if ($purchaseId === null || !is_numeric($purchaseId)) {
            return;
        }

        $purchase = $this->purchaseRepository->find((int) $purchaseId);
        if ($purchase === null) {
            throw new \RuntimeException(\sprintf('Product purchase %s not found for Stripe checkout session %s.', $purchaseId, $session->id));
        }

        if ($purchase->getStripeCheckoutSessionId() !== null && $purchase->getStripeCheckoutSessionId() !== $session->id) {
            throw new \RuntimeException(\sprintf('Stripe checkout session %s does not match product purchase %d.', $session->id, $purchase->getId()));
        }

        if ($session->payment_status === 'paid') {
            $this->assertSessionMatchesPurchase($session, $purchase, $metadata);
            $purchase->markPaid();
        }

        $purchase
            ->setStripeCheckoutSessionId($session->id)
            ->setEmail($session->customer_details?->email ?? $session->customer_email)
            ->setStripePaymentIntentId($this->normalizeStripeId($session->payment_intent))
            ->setStripeCustomerId($this->normalizeStripeId($session->customer));

        $this->entityManager->flush();
    }

    /**
     * @param array<string,mixed> $metadata
     */
    private function assertSessionMatchesPurchase(Session $session, ProductPurchase $purchase, array $metadata): void
    {
        $product = $purchase->getProduct();
        if ($session->amount_total !== $purchase->getAmountTotal()) {
            throw new \RuntimeException(\sprintf(
                'Stripe checkout session %s amount %s does not match product purchase %d amount %d.',
                $session->id,
                (string) $session->amount_total,
                $purchase->getId(),
                $purchase->getAmountTotal()
            ));
        }

        if (strtolower((string) $session->currency) !== $purchase->getCurrency()) {
            throw new \RuntimeException(\sprintf(
                'Stripe checkout session %s currency %s does not match product purchase %d currency %s.',
                $session->id,
                (string) $session->currency,
                $purchase->getId(),
                $purchase->getCurrency()
            ));
        }

        if (($metadata['product_code'] ?? null) !== $product->getCode()) {
            throw new \RuntimeException(\sprintf(
                'Stripe checkout session %s product code does not match product purchase %d.',
                $session->id,
                $purchase->getId()
            ));
        }
    }

    private function normalizeStripeId(mixed $value): ?string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (\is_object($value) && property_exists($value, 'id') && \is_string($value->id)) {
            return $value->id;
        }

        return null;
    }
}
