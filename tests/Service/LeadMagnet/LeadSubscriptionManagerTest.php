<?php

namespace App\Tests\Service\LeadMagnet;

use App\Entity\LeadSubscriber;
use App\Repository\LeadSubscriberRepository;
use App\Service\LeadMagnet\LeadFollowUpSchedule;
use App\Service\LeadMagnet\LeadMailer;
use App\Service\LeadMagnet\LeadSubscriptionManager;
use App\Service\Product\PauBundleProductCatalog;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

final class LeadSubscriptionManagerTest extends TestCase
{
    private const PACK = 'pau_fisica_madrid_1996_2025';

    public function testANewEmailIsStoredWithConsentAndGetsTheLink(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $persisted = null;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->willReturnCallback(
            static function (LeadSubscriber $subscriber) use (&$persisted): void {
                $persisted = $subscriber;
            }
        );
        $em->expects(self::once())->method('flush');

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::once())->method('sendConfirmation');

        $this->createManager($em, $mailer, existing: null)
            ->subscribe('  Ana@Example.COM ', self::PACK, '1.2.3.4', $now);

        self::assertInstanceOf(LeadSubscriber::class, $persisted);
        self::assertSame('ana@example.com', $persisted->getEmail());
        self::assertSame('1.2.3.4', $persisted->getConsentIp());
        self::assertSame(self::PACK, $persisted->getSourcePackCode());
        self::assertEquals($now, $persisted->getConfirmationSentAt());
        self::assertFalse($persisted->isConfirmed());
    }

    public function testAnUnknownPackCodeIsNotStored(): void
    {
        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->willReturnCallback(
            static function (LeadSubscriber $subscriber) use (&$persisted): void {
                $persisted = $subscriber;
            }
        );

        $this->createManager($em, $this->createStub(LeadMailer::class), existing: null)
            ->subscribe('ana@example.com', 'not-a-pack', null, new DateTimeImmutable());

        self::assertInstanceOf(LeadSubscriber::class, $persisted);
        self::assertNull($persisted->getSourcePackCode());
    }

    public function testTheSameEmailWithinTheCooldownSendsNothing(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $existing = new LeadSubscriber('ana@example.com', $now);
        $existing->markConfirmationSent($now->modify('-5 minutes'));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::never())->method('sendConfirmation');

        $this->createManager($em, $mailer, $existing)->subscribe('ana@example.com', null, null, $now);
    }

    public function testAKnownEmailAfterTheCooldownGetsTheLinkAgainAndKeepsItsConfirmation(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $existing = new LeadSubscriber('ana@example.com', $now->modify('-3 days'));
        $existing->markConfirmationSent($now->modify('-3 days'));
        $existing->confirm($now->modify('-3 days'));

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::once())->method('sendConfirmation')->with($existing);

        $this->createManager($this->createStub(EntityManagerInterface::class), $mailer, $existing)
            ->subscribe('ana@example.com', self::PACK, null, $now);

        self::assertTrue($existing->isConfirmed());
        self::assertSame(self::PACK, $existing->getSourcePackCode());
    }

    public function testSigningUpAfterAnUnsubscribeNeedsANewConfirmation(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $existing = new LeadSubscriber('ana@example.com', $now->modify('-30 days'));
        $existing->confirm($now->modify('-30 days'));
        $existing->unsubscribe($now->modify('-20 days'));

        $this->createManager($this->createStub(EntityManagerInterface::class), $this->createStub(LeadMailer::class), $existing)
            ->subscribe('ana@example.com', null, '5.6.7.8', $now);

        self::assertFalse($existing->isUnsubscribed());
        self::assertFalse($existing->isConfirmed());
        self::assertNull($existing->getNextFollowUpAt());
        self::assertEquals($now, $existing->getConsentAt());
    }

    public function testNothingIsStoredWhenTheEmailCannotBeSent(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $mailer = $this->createStub(LeadMailer::class);
        $mailer->method('sendConfirmation')->willThrowException(new TransportException('down'));

        $this->expectException(TransportException::class);
        $this->createManager($em, $mailer, existing: null)
            ->subscribe('ana@example.com', null, null, new DateTimeImmutable());
    }

    public function testConfirmingPlansTheFirstFollowUp(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $subscriber = new LeadSubscriber('ana@example.com', $now);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        $result = $this->createManager($em, $this->createStub(LeadMailer::class), $subscriber)
            ->confirm($subscriber->getConfirmationToken(), $now);

        self::assertSame($subscriber, $result);
        self::assertEquals($now, $subscriber->getConfirmedAt());
        self::assertEquals($now->modify('+2 days'), $subscriber->getNextFollowUpAt());
    }

    public function testConfirmingTwiceKeepsTheFirstDate(): void
    {
        $first = new DateTimeImmutable('2026-11-20 10:00:00');
        $subscriber = new LeadSubscriber('ana@example.com', $first);
        $subscriber->confirm($first);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $this->createManager($em, $this->createStub(LeadMailer::class), $subscriber)
            ->confirm($subscriber->getConfirmationToken(), new DateTimeImmutable('2026-11-23 10:00:00'));

        self::assertEquals($first, $subscriber->getConfirmedAt());
    }

    public function testAnUnsubscribedAddressCanStillDownloadButIsNotConfirmedAgain(): void
    {
        $subscriber = new LeadSubscriber('ana@example.com', new DateTimeImmutable('2026-11-20'));
        $subscriber->unsubscribe(new DateTimeImmutable('2026-11-21'));

        $result = $this->createManager($this->createStub(EntityManagerInterface::class), $this->createStub(LeadMailer::class), $subscriber)
            ->confirm($subscriber->getConfirmationToken(), new DateTimeImmutable('2026-11-23'));

        self::assertSame($subscriber, $result);
        self::assertFalse($subscriber->isConfirmed());
        self::assertNull($subscriber->getNextFollowUpAt());
    }

    public function testAnUnknownTokenIsRefused(): void
    {
        $manager = $this->createManager($this->createStub(EntityManagerInterface::class), $this->createStub(LeadMailer::class), existing: null);

        self::assertNull($manager->confirm(str_repeat('a', 64), new DateTimeImmutable()));
        self::assertFalse($manager->unsubscribe(str_repeat('a', 64), new DateTimeImmutable()));
    }

    public function testUnsubscribingStopsTheFollowUps(): void
    {
        $now = new DateTimeImmutable('2026-11-23 10:00:00');
        $subscriber = new LeadSubscriber('ana@example.com', $now);
        $subscriber->confirm($now)->scheduleNextFollowUp($now->modify('+2 days'));

        self::assertTrue(
            $this->createManager($this->createStub(EntityManagerInterface::class), $this->createStub(LeadMailer::class), $subscriber)
                ->unsubscribe($subscriber->getUnsubscribeToken(), $now)
        );
        self::assertTrue($subscriber->isUnsubscribed());
        self::assertNull($subscriber->getNextFollowUpAt());
    }

    private function createManager(EntityManagerInterface $em, LeadMailer $mailer, ?LeadSubscriber $existing): LeadSubscriptionManager
    {
        $repository = $this->createStub(LeadSubscriberRepository::class);
        $repository->method('findOneByEmail')->willReturnCallback(
            static fn (string $email): ?LeadSubscriber => $existing?->getEmail() === $email ? $existing : null
        );
        $repository->method('findOneByConfirmationToken')->willReturnCallback(
            static fn (string $token): ?LeadSubscriber => $existing?->getConfirmationToken() === $token ? $existing : null
        );
        $repository->method('findOneByUnsubscribeToken')->willReturnCallback(
            static fn (string $token): ?LeadSubscriber => $existing?->getUnsubscribeToken() === $token ? $existing : null
        );

        return new LeadSubscriptionManager($em, $repository, $mailer, new LeadFollowUpSchedule(), new PauBundleProductCatalog());
    }
}
