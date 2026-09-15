<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Enum\PremiumProvider;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserPremiumProviderTest extends TestCase
{
    public function testFirstGrantRecordsTheProvider(): void
    {
        $user = new User();
        $until = new DateTimeImmutable('+1 month');

        self::assertTrue($user->grantPremiumUntil($until, PremiumProvider::APPLE));
        self::assertSame($until, $user->getPremiumUntil());
        self::assertSame(PremiumProvider::APPLE, $user->getPremiumProvider());
        self::assertTrue($user->isPremium());
    }

    public function testAppleDoesNotShortenAccessAlreadyPaidThroughStripe(): void
    {
        $user = new User();
        $stripeUntil = new DateTimeImmutable('+1 year');
        $user->grantPremiumUntil($stripeUntil, PremiumProvider::STRIPE);

        self::assertFalse($user->grantPremiumUntil(new DateTimeImmutable('+1 month'), PremiumProvider::APPLE));
        self::assertSame($stripeUntil, $user->getPremiumUntil());
        self::assertSame(PremiumProvider::STRIPE, $user->getPremiumProvider());
    }

    public function testAppleTakesOverWhenItGivesMoreTime(): void
    {
        $user = new User();
        $user->grantPremiumUntil(new DateTimeImmutable('+1 month'), PremiumProvider::STRIPE);
        $appleUntil = new DateTimeImmutable('+1 year');

        self::assertTrue($user->grantPremiumUntil($appleUntil, PremiumProvider::APPLE));
        self::assertSame($appleUntil, $user->getPremiumUntil());
        self::assertSame(PremiumProvider::APPLE, $user->getPremiumProvider());
    }

    public function testStripeCanShortenItsOwnAccess(): void
    {
        $user = new User();
        $user->grantPremiumUntil(new DateTimeImmutable('+1 year'), PremiumProvider::STRIPE);
        $shorter = new DateTimeImmutable('+1 day');

        self::assertTrue($user->grantPremiumUntil($shorter, PremiumProvider::STRIPE));
        self::assertSame($shorter, $user->getPremiumUntil());
    }

    public function testAppleCanGrantAgainOnceStripeAccessHasExpired(): void
    {
        $user = new User();
        $user->grantPremiumUntil(new DateTimeImmutable('-1 day'), PremiumProvider::STRIPE);
        $appleUntil = new DateTimeImmutable('+1 month');

        self::assertTrue($user->grantPremiumUntil($appleUntil, PremiumProvider::APPLE));
        self::assertSame($appleUntil, $user->getPremiumUntil());
    }

    public function testRefundEndsAccessImmediately(): void
    {
        $user = new User();
        $user->grantPremiumUntil(new DateTimeImmutable('+1 year'), PremiumProvider::APPLE);

        self::assertTrue($user->revokePremium(PremiumProvider::APPLE));
        self::assertFalse($user->isPremium());
    }

    public function testAppleRefundDoesNotEndStripeAccess(): void
    {
        $user = new User();
        $until = new DateTimeImmutable('+1 year');
        $user->grantPremiumUntil($until, PremiumProvider::STRIPE);

        self::assertFalse($user->revokePremium(PremiumProvider::APPLE));
        self::assertSame($until, $user->getPremiumUntil());
        self::assertTrue($user->isPremium());
    }
}
