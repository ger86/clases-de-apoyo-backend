<?php

namespace App\Service\LeadMagnet;

use DateTimeImmutable;

/**
 * When each follow-up email of the free exam pack goes out, counted from the
 * day the subscriber confirmed the email address.
 */
final class LeadFollowUpSchedule
{
    /** Delay from the confirmation for each follow-up step (1-based). */
    private const DELAYS = [
        1 => '+2 days',
        2 => '+5 days',
        3 => '+9 days',
    ];

    /** Two follow-ups never go out closer than this, even after a cron outage. */
    private const MIN_GAP = '+1 day';

    public function getLastStep(): int
    {
        return \count(self::DELAYS);
    }

    /**
     * The due date of the next follow-up after $stepsSent emails, or null when
     * the sequence is over.
     */
    public function next(int $stepsSent, DateTimeImmutable $confirmedAt, DateTimeImmutable $now): ?DateTimeImmutable
    {
        $delay = self::DELAYS[$stepsSent + 1] ?? null;
        if ($delay === null) {
            return null;
        }

        $due = $confirmedAt->modify($delay);
        if ($stepsSent === 0) {
            return $due;
        }

        $earliest = $now->modify(self::MIN_GAP);

        return $due > $earliest ? $due : $earliest;
    }
}
