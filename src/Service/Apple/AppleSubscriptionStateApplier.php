<?php

namespace App\Service\Apple;

use App\Entity\User;
use App\Enum\PremiumProvider;
use DateTimeImmutable;
use Readdle\AppStoreServerAPI\RenewalInfo;
use Readdle\AppStoreServerAPI\TransactionInfo;

/**
 * Turns one Apple transaction into premium access on the account.
 *
 * Access runs until the expiry date Apple reports plus six hours, the same small
 * buffer the Stripe invoice path uses, so a slow renewal does not lock a paying user out.
 */
final class AppleSubscriptionStateApplier
{
    private const BUFFER = '+6 hours';

    public function apply(User $user, TransactionInfo $transactionInfo, ?RenewalInfo $renewalInfo): void
    {
        $user->setAppleOriginalTransactionId($transactionInfo->getOriginalTransactionId());

        if ($transactionInfo->getRevocationDate() !== null) {
            $this->revoke($user);

            return;
        }

        $expiresAt = $this->latestExpiry($transactionInfo, $renewalInfo);
        if ($expiresAt === null) {
            return;
        }

        $user->grantPremiumUntil($expiresAt->modify(self::BUFFER), PremiumProvider::APPLE);
        $user->setAppleSubscriptionStatus($this->describe($renewalInfo));
    }

    public function revoke(User $user): void
    {
        $user->revokePremium(PremiumProvider::APPLE);
        $user->setAppleSubscriptionStatus('revoked');
    }

    private function latestExpiry(TransactionInfo $transactionInfo, ?RenewalInfo $renewalInfo): ?DateTimeImmutable
    {
        $expiresAt = self::fromAppleTimestamp($transactionInfo->getExpiresDate());

        // Apple keeps serving the subscription during a billing grace period, so the user
        // must keep their access even though the paid period already ended.
        $graceExpiresAt = self::fromAppleTimestamp($renewalInfo?->getGracePeriodExpiresDate());
        if ($graceExpiresAt !== null && ($expiresAt === null || $graceExpiresAt > $expiresAt)) {
            return $graceExpiresAt;
        }

        return $expiresAt;
    }

    private function describe(?RenewalInfo $renewalInfo): string
    {
        if ($renewalInfo === null) {
            return 'active';
        }

        return $renewalInfo->getAutoRenewStatus() === RenewalInfo::AUTO_RENEW_STATUS__ON
            ? 'active'
            : 'cancelled';
    }

    /**
     * Apple reports every date as milliseconds since the epoch.
     */
    private static function fromAppleTimestamp(?int $milliseconds): ?DateTimeImmutable
    {
        if ($milliseconds === null) {
            return null;
        }

        return (new DateTimeImmutable())->setTimestamp(intdiv($milliseconds, 1000));
    }
}
