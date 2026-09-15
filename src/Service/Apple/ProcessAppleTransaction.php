<?php

namespace App\Service\Apple;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Readdle\AppStoreServerAPI\Exception\AppStoreServerAPIException;
use Readdle\AppStoreServerAPI\LastTransactionsItem;
use Readdle\AppStoreServerAPI\TransactionInfo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Called by the app right after a purchase and when the user restores purchases.
 *
 * The app only sends a transaction id. The subscription itself is read straight from
 * Apple, so a made-up body cannot grant premium. What stops someone from claiming
 * another person's purchase is the appAccountToken: the app passes it to StoreKit at
 * purchase time and Apple returns it with the transaction.
 */
final class ProcessAppleTransaction
{
    public function __construct(
        private AppleClient $appleClient,
        private AppleSubscriptionStateApplier $applier,
        private UserRepository $userRepository,
        private EnsureAppAccountToken $ensureAppAccountToken,
        private EntityManagerInterface $em,
        #[Autowire('%app.apple.monthly_product_id%')]
        private string $monthlyProductId,
        #[Autowire('%app.apple.yearly_product_id%')]
        private string $yearlyProductId
    ) {
    }

    public function __invoke(User $user, string $transactionId): void
    {
        try {
            $statuses = $this->appleClient->getAllSubscriptionStatuses($transactionId);
        } catch (AppStoreServerAPIException $exception) {
            throw new AppleTransactionException(
                'Apple no reconoce esa compra: ' . $exception->getMessage(),
                'apple_transaction_not_found',
                Response::HTTP_NOT_FOUND
            );
        }

        if ($statuses->getBundleId() !== $this->appleClient->getBundleId()) {
            throw new AppleTransactionException(
                'Esa compra no pertenece a esta aplicación.',
                'apple_wrong_bundle',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $item = $this->findOurSubscription($statuses->getData());
        if ($item === null) {
            throw new AppleTransactionException(
                'Esa compra no corresponde a una suscripción de Clases de Apoyo.',
                'apple_unknown_product',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $transactionInfo = $item->getTransactionInfo();
        $this->assertBelongsToUser($user, $transactionInfo);

        $this->applier->apply($user, $transactionInfo, $item->getRenewalInfo());
        $this->em->flush();
    }

    /**
     * @param iterable<mixed> $subscriptionGroups
     */
    private function findOurSubscription(iterable $subscriptionGroups): ?LastTransactionsItem
    {
        $productIds = [$this->monthlyProductId, $this->yearlyProductId];
        $found = null;

        foreach ($subscriptionGroups as $subscriptionGroup) {
            foreach ($subscriptionGroup->getLastTransactions() as $item) {
                if (!\in_array($item->getTransactionInfo()->getProductId(), $productIds, true)) {
                    continue;
                }

                // Keep the transaction that expires last, so an upgrade or a resubscription
                // is not overwritten by an older expired one in the same group.
                if ($found === null || self::expiry($item) > self::expiry($found)) {
                    $found = $item;
                }
            }
        }

        return $found;
    }

    private function assertBelongsToUser(User $user, TransactionInfo $transactionInfo): void
    {
        $appAccountToken = $transactionInfo->getAppAccountToken();
        $expectedToken = ($this->ensureAppAccountToken)($user);

        if ($appAccountToken !== null && $appAccountToken !== '') {
            if (strcasecmp($appAccountToken, $expectedToken) !== 0) {
                throw new AppleTransactionException(
                    'Esta suscripción pertenece a otra cuenta de Clases de Apoyo.',
                    'apple_transaction_owned_by_other_account',
                    Response::HTTP_CONFLICT
                );
            }

            return;
        }

        // Purchases made before the account token existed carry no token. They can still be
        // restored, but only onto an account that no other account has claimed already.
        $owner = $this->userRepository->findOneByAppleOriginalTransactionId(
            $transactionInfo->getOriginalTransactionId()
        );

        if ($owner !== null && $owner->getId() !== $user->getId()) {
            throw new AppleTransactionException(
                'Esta suscripción pertenece a otra cuenta de Clases de Apoyo.',
                'apple_transaction_owned_by_other_account',
                Response::HTTP_CONFLICT
            );
        }
    }

    private static function expiry(LastTransactionsItem $item): int
    {
        return $item->getTransactionInfo()->getExpiresDate() ?? 0;
    }
}
