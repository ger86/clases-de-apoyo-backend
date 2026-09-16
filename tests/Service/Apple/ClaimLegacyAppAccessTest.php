<?php

namespace App\Tests\Service\Apple;

use App\Entity\User;
use App\Enum\PremiumProvider;
use App\Service\Apple\AppleTransactionException;
use App\Service\Apple\ClaimLegacyAppAccess;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ClaimLegacyAppAccessTest extends TestCase
{
    private const CUTOFF = '2026-10-01';

    public function testAPurchaseMadeWhileTheAppWasPaidGrantsAFreeYear(): void
    {
        $user = new User();

        ($this->service())($user, new DateTimeImmutable('2024-05-04'), 'app-transaction-1');

        self::assertTrue($user->isPremium());
        self::assertSame(PremiumProvider::LEGACY_APP, $user->getPremiumProvider());
        self::assertTrue($user->hasClaimedLegacyAppAccess());
        self::assertSame('app-transaction-1', $user->getLegacyAppTransactionId());
        self::assertSame(
            (new DateTimeImmutable('+1 year'))->format('Y-m-d'),
            $user->getPremiumUntil()?->format('Y-m-d')
        );
    }

    public function testADownloadMadeAfterTheAppBecameFreeEarnsNothing(): void
    {
        $user = new User();

        $this->expectException(AppleTransactionException::class);

        ($this->service())($user, new DateTimeImmutable('2026-11-20'), null);
    }

    public function testTheSameAccountCannotClaimTwice(): void
    {
        $user = new User();
        ($this->service())($user, new DateTimeImmutable('2024-05-04'), null);
        $grantedUntil = $user->getPremiumUntil();

        try {
            ($this->service())($user, new DateTimeImmutable('2024-05-04'), null);
            self::fail('A second claim must be refused.');
        } catch (AppleTransactionException $exception) {
            self::assertSame('legacy_access_already_claimed', $exception->errorCode);
        }

        self::assertSame($grantedUntil, $user->getPremiumUntil());
    }

    public function testALongerStripeSubscriptionIsNotCutShort(): void
    {
        $user = new User();
        $stripeUntil = new DateTimeImmutable('+3 years');
        $user->grantPremiumUntil($stripeUntil, PremiumProvider::STRIPE);

        ($this->service())($user, new DateTimeImmutable('2024-05-04'), null);

        self::assertSame($stripeUntil, $user->getPremiumUntil());
        self::assertSame(PremiumProvider::STRIPE, $user->getPremiumProvider());
        // The year is spent even so, because the account has already been paid further ahead.
        self::assertTrue($user->hasClaimedLegacyAppAccess());
    }

    public function testAnAppleSubscriptionCannotCutTheFreeYearShort(): void
    {
        $user = new User();
        ($this->service())($user, new DateTimeImmutable('2024-05-04'), null);
        $freeYearUntil = $user->getPremiumUntil();

        $user->grantPremiumUntil(new DateTimeImmutable('+1 month'), PremiumProvider::APPLE);

        self::assertSame($freeYearUntil, $user->getPremiumUntil());
        self::assertSame(PremiumProvider::LEGACY_APP, $user->getPremiumProvider());
    }

    private function service(): ClaimLegacyAppAccess
    {
        return new ClaimLegacyAppAccess($this->createStub(EntityManagerInterface::class), self::CUTOFF);
    }
}
