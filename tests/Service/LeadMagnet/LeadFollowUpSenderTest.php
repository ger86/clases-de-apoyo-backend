<?php

namespace App\Tests\Service\LeadMagnet;

use App\Entity\LeadSubscriber;
use App\Repository\LeadSubscriberRepository;
use App\Service\LeadMagnet\LeadFollowUpSchedule;
use App\Service\LeadMagnet\LeadFollowUpSender;
use App\Service\LeadMagnet\LeadMailer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

final class LeadFollowUpSenderTest extends TestCase
{
    public function testTheNextStepIsSentAndTheOneAfterIsPlanned(): void
    {
        $confirmedAt = new DateTimeImmutable('2026-11-20 10:00:00');
        $now = new DateTimeImmutable('2026-11-22 10:00:00');
        $subscriber = $this->confirmedSubscriber($confirmedAt);

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::once())->method('sendFollowUp')->with($subscriber, 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');

        self::assertSame(1, $this->createSender($em, $mailer, [$subscriber])->sendDue($now, 50));
        self::assertSame(1, $subscriber->getFollowUpStep());
        self::assertEquals($now, $subscriber->getLastFollowUpAt());
        self::assertEquals($confirmedAt->modify('+5 days'), $subscriber->getNextFollowUpAt());
    }

    public function testTheSequenceEndsAfterTheLastStep(): void
    {
        $confirmedAt = new DateTimeImmutable('2026-11-01 10:00:00');
        $subscriber = $this->confirmedSubscriber($confirmedAt);
        $subscriber->markFollowUpSent($confirmedAt)->markFollowUpSent($confirmedAt);

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::once())->method('sendFollowUp')->with($subscriber, 3);

        $this->createSender($this->createStub(EntityManagerInterface::class), $mailer, [$subscriber])
            ->sendDue(new DateTimeImmutable('2026-11-10 10:00:00'), 50);

        self::assertSame(3, $subscriber->getFollowUpStep());
        self::assertNull($subscriber->getNextFollowUpAt());
    }

    public function testASubscriberPastTheLastStepGetsNothingAndLeavesTheQueue(): void
    {
        $confirmedAt = new DateTimeImmutable('2026-11-01 10:00:00');
        $subscriber = $this->confirmedSubscriber($confirmedAt);
        $subscriber->markFollowUpSent($confirmedAt)->markFollowUpSent($confirmedAt)->markFollowUpSent($confirmedAt);

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::never())->method('sendFollowUp');

        self::assertSame(0, $this->createSender($this->createStub(EntityManagerInterface::class), $mailer, [$subscriber])
            ->sendDue(new DateTimeImmutable('2026-11-20'), 50));
        self::assertNull($subscriber->getNextFollowUpAt());
    }

    public function testAMailErrorStopsTheRunWithoutMarkingTheStep(): void
    {
        $confirmedAt = new DateTimeImmutable('2026-11-20 10:00:00');
        $first = $this->confirmedSubscriber($confirmedAt);
        $second = $this->confirmedSubscriber($confirmedAt);

        $mailer = $this->createMock(LeadMailer::class);
        $mailer->expects(self::once())->method('sendFollowUp')->willThrowException(new TransportException('down'));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        try {
            $this->createSender($em, $mailer, [$first, $second])->sendDue(new DateTimeImmutable('2026-11-22 10:00:00'), 50);
            self::fail('The mail error should reach the command.');
        } catch (TransportException) {
        }

        self::assertSame(0, $first->getFollowUpStep());
        self::assertSame(0, $second->getFollowUpStep());
    }

    private function confirmedSubscriber(DateTimeImmutable $confirmedAt): LeadSubscriber
    {
        $subscriber = new LeadSubscriber(uniqid('lead', true) . '@example.com', $confirmedAt);
        $subscriber->confirm($confirmedAt)->scheduleNextFollowUp($confirmedAt->modify('+2 days'));

        return $subscriber;
    }

    /**
     * @param list<LeadSubscriber> $due
     */
    private function createSender(EntityManagerInterface $em, LeadMailer $mailer, array $due): LeadFollowUpSender
    {
        $repository = $this->createStub(LeadSubscriberRepository::class);
        $repository->method('findDueForFollowUp')->willReturn($due);

        return new LeadFollowUpSender($em, $repository, $mailer, new LeadFollowUpSchedule());
    }
}
