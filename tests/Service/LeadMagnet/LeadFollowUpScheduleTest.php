<?php

namespace App\Tests\Service\LeadMagnet;

use App\Service\LeadMagnet\LeadFollowUpSchedule;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LeadFollowUpScheduleTest extends TestCase
{
    public function testStepsAreCountedFromTheConfirmation(): void
    {
        $schedule = new LeadFollowUpSchedule();
        $confirmedAt = new DateTimeImmutable('2026-11-20 10:00:00');

        self::assertEquals($confirmedAt->modify('+2 days'), $schedule->next(0, $confirmedAt, $confirmedAt));
        self::assertEquals($confirmedAt->modify('+5 days'), $schedule->next(1, $confirmedAt, $confirmedAt->modify('+2 days')));
        self::assertEquals($confirmedAt->modify('+9 days'), $schedule->next(2, $confirmedAt, $confirmedAt->modify('+5 days')));
        self::assertNull($schedule->next(3, $confirmedAt, $confirmedAt->modify('+9 days')));
    }

    public function testAfterAnOutageTwoEmailsAreNeverSentOnTheSameDay(): void
    {
        $schedule = new LeadFollowUpSchedule();
        $confirmedAt = new DateTimeImmutable('2026-11-01 10:00:00');
        $now = new DateTimeImmutable('2026-11-20 10:00:00');

        self::assertEquals($now->modify('+1 day'), $schedule->next(1, $confirmedAt, $now));
    }
}
