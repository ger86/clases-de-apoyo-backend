<?php

namespace App\Service\LeadMagnet;

use App\Entity\LeadSubscriber;
use App\Repository\LeadSubscriberRepository;
use App\Service\Product\PauBundleProductCatalog;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Signup, confirmation and unsubscribe for the free exam pack.
 *
 * The answer to a signup is always the same ("check your inbox"), so the form
 * never tells a visitor whether an address is already on the list.
 */
final class LeadSubscriptionManager
{
    /** A second email to the same address waits this long, so the form cannot flood an inbox. */
    private const RESEND_COOLDOWN = '-10 minutes';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LeadSubscriberRepository $repository,
        private LeadMailer $leadMailer,
        private LeadFollowUpSchedule $schedule,
        private PauBundleProductCatalog $productCatalog
    ) {
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * Stores the consent and sends the email with the download link. Clicking
     * that link is what confirms the address.
     */
    public function subscribe(string $email, ?string $packCode, ?string $ip, DateTimeImmutable $now): void
    {
        $email = self::normalizeEmail($email);
        $packCode = $packCode !== null && $this->productCatalog->findByCode($packCode) !== null ? $packCode : null;

        $subscriber = $this->repository->findOneByEmail($email);
        if ($subscriber === null) {
            $subscriber = new LeadSubscriber($email, $now);
            $subscriber->giveConsent($now, $ip);
            $this->entityManager->persist($subscriber);
        } else {
            $lastSent = $subscriber->getConfirmationSentAt();
            if ($lastSent !== null && $lastSent > $now->modify(self::RESEND_COOLDOWN)) {
                return;
            }

            if ($subscriber->isUnsubscribed()) {
                // Someone typed this address again after an unsubscribe. Until the
                // owner clicks the new link, nothing more is sent.
                $subscriber->resubscribe($now, $ip);
            }
        }

        if ($subscriber->getSourcePackCode() === null) {
            $subscriber->setSourcePackCode($packCode);
        }

        // Send first: when the mail server fails, nothing is stored and the
        // visitor can try again without waiting for the cooldown.
        $this->leadMailer->sendConfirmation($subscriber);
        $subscriber->markConfirmationSent($now);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            // Another request stored the same address a moment ago; the email went out anyway.
        }
    }

    /**
     * Confirms the address behind a download link. Returns null for an unknown token.
     */
    public function confirm(string $token, DateTimeImmutable $now): ?LeadSubscriber
    {
        $subscriber = $this->repository->findOneByConfirmationToken($token);
        if ($subscriber === null) {
            return null;
        }

        if (!$subscriber->isConfirmed() && !$subscriber->isUnsubscribed()) {
            $subscriber->confirm($now);
            $subscriber->scheduleNextFollowUp(
                $this->schedule->next($subscriber->getFollowUpStep(), $now, $now)
            );
            $this->entityManager->flush();
        }

        return $subscriber;
    }

    public function findByUnsubscribeToken(string $token): ?LeadSubscriber
    {
        return $this->repository->findOneByUnsubscribeToken($token);
    }

    /**
     * Returns false for an unknown token.
     */
    public function unsubscribe(string $token, DateTimeImmutable $now): bool
    {
        $subscriber = $this->repository->findOneByUnsubscribeToken($token);
        if ($subscriber === null) {
            return false;
        }

        if (!$subscriber->isUnsubscribed()) {
            $subscriber->unsubscribe($now);
            $this->entityManager->flush();
        }

        return true;
    }
}
