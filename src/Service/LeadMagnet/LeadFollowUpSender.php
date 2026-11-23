<?php

namespace App\Service\LeadMagnet;

use App\Repository\LeadSubscriberRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Sends the follow-up emails that are due. Each subscriber is saved right after
 * its email, so a crash halfway through never sends the same step twice.
 */
final class LeadFollowUpSender
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LeadSubscriberRepository $repository,
        private LeadMailer $leadMailer,
        private LeadFollowUpSchedule $schedule
    ) {
    }

    /**
     * Returns the number of emails sent. A mail server error stops the run and
     * is thrown; the subscribers not reached stay due for the next run.
     */
    public function sendDue(DateTimeImmutable $now, int $limit): int
    {
        $sent = 0;
        foreach ($this->repository->findDueForFollowUp($now, $limit) as $subscriber) {
            $confirmedAt = $subscriber->getConfirmedAt();
            $step = $subscriber->getFollowUpStep() + 1;
            if ($confirmedAt === null || $step > $this->schedule->getLastStep()) {
                $subscriber->scheduleNextFollowUp(null);
                $this->entityManager->flush();
                continue;
            }

            $this->leadMailer->sendFollowUp($subscriber, $step);
            $subscriber->markFollowUpSent($now);
            $subscriber->scheduleNextFollowUp($this->schedule->next($step, $confirmedAt, $now));
            $this->entityManager->flush();
            ++$sent;
        }

        return $sent;
    }
}
