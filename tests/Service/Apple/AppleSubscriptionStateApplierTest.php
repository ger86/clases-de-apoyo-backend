<?php

namespace App\Tests\Service\Apple;

use App\Entity\User;
use App\Enum\PremiumProvider;
use App\Service\Apple\AppleSubscriptionStateApplier;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Readdle\AppStoreServerAPI\RenewalInfo;
use Readdle\AppStoreServerAPI\TransactionInfo;

final class AppleSubscriptionStateApplierTest extends TestCase
{
    public function testAPaidPeriodGrantsPremiumWithASixHourBuffer(): void
    {
        $expiresAt = new DateTimeImmutable('+30 days');
        $user = new User();

        (new AppleSubscriptionStateApplier())->apply($user, $this->transaction($expiresAt), null);

        self::assertTrue($user->isPremium());
        self::assertSame(PremiumProvider::APPLE, $user->getPremiumProvider());
        self::assertSame('1000000000000001', $user->getAppleOriginalTransactionId());
        self::assertSame(
            $expiresAt->modify('+6 hours')->format('Y-m-d H:i:s'),
            $user->getPremiumUntil()?->format('Y-m-d H:i:s')
        );
    }

    public function testAnExpiredPeriodLeavesTheUserWithoutPremium(): void
    {
        $user = new User();

        (new AppleSubscriptionStateApplier())->apply($user, $this->transaction(new DateTimeImmutable('-2 days')), null);

        self::assertFalse($user->isPremium());
    }

    public function testBillingGracePeriodKeepsAccessAfterThePaidPeriodEnds(): void
    {
        $graceEndsAt = new DateTimeImmutable('+10 days');
        $user = new User();

        (new AppleSubscriptionStateApplier())->apply(
            $user,
            $this->transaction(new DateTimeImmutable('-1 day')),
            $this->renewal($graceEndsAt)
        );

        self::assertTrue($user->isPremium());
        self::assertSame(
            $graceEndsAt->modify('+6 hours')->format('Y-m-d H:i:s'),
            $user->getPremiumUntil()?->format('Y-m-d H:i:s')
        );
    }

    public function testARefundedTransactionEndsAccessRightAway(): void
    {
        $user = new User();
        $applier = new AppleSubscriptionStateApplier();
        $applier->apply($user, $this->transaction(new DateTimeImmutable('+30 days')), null);

        $applier->apply($user, $this->transaction(new DateTimeImmutable('+30 days'), revoked: true), null);

        self::assertFalse($user->isPremium());
        self::assertSame('revoked', $user->getAppleSubscriptionStatus());
    }

    public function testCancelledAutoRenewalKeepsAccessUntilTheEndOfThePaidPeriod(): void
    {
        $expiresAt = new DateTimeImmutable('+20 days');
        $user = new User();

        (new AppleSubscriptionStateApplier())->apply(
            $user,
            $this->transaction($expiresAt),
            $this->renewal(null, autoRenewStatus: RenewalInfo::AUTO_RENEW_STATUS__OFF)
        );

        self::assertTrue($user->isPremium());
        self::assertSame('cancelled', $user->getAppleSubscriptionStatus());
    }

    private function transaction(DateTimeImmutable $expiresAt, bool $revoked = false): TransactionInfo
    {
        return TransactionInfo::createFromRawTransactionInfo([
            'bundleId' => 'ger.Clases-de-apoyo',
            'environment' => 'Sandbox',
            'inAppOwnershipType' => TransactionInfo::IN_APP_OWNERSHIP_TYPE__PURCHASED,
            'originalTransactionId' => '1000000000000001',
            'transactionId' => '1000000000000002',
            'productId' => 'premium_monthly',
            'type' => TransactionInfo::TYPE__AUTO_RENEWABLE_SUBSCRIPTION,
            'quantity' => 1,
            'purchaseDate' => self::toAppleTimestamp(new DateTimeImmutable('-1 day')),
            'originalPurchaseDate' => self::toAppleTimestamp(new DateTimeImmutable('-1 day')),
            'signedDate' => self::toAppleTimestamp(new DateTimeImmutable()),
            'expiresDate' => self::toAppleTimestamp($expiresAt),
            'revocationDate' => $revoked ? self::toAppleTimestamp(new DateTimeImmutable()) : null,
        ]);
    }

    private function renewal(
        ?DateTimeImmutable $gracePeriodExpiresAt,
        int $autoRenewStatus = RenewalInfo::AUTO_RENEW_STATUS__ON
    ): RenewalInfo {
        return RenewalInfo::createFromRawRenewalInfo([
            'autoRenewProductId' => 'premium_monthly',
            'autoRenewStatus' => $autoRenewStatus,
            'environment' => 'Sandbox',
            'originalTransactionId' => '1000000000000001',
            'productId' => 'premium_monthly',
            'recentSubscriptionStartDate' => self::toAppleTimestamp(new DateTimeImmutable('-1 month')),
            'signedDate' => self::toAppleTimestamp(new DateTimeImmutable()),
            'gracePeriodExpiresDate' => $gracePeriodExpiresAt === null
                ? null
                : self::toAppleTimestamp($gracePeriodExpiresAt),
        ]);
    }

    /**
     * Apple reports every date as milliseconds since the epoch.
     */
    private static function toAppleTimestamp(DateTimeImmutable $date): int
    {
        return $date->getTimestamp() * 1000;
    }
}
