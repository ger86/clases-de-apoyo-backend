<?php

namespace App\Entity;

use App\Repository\LeadSubscriberRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * A visitor who left an email to get the free exam pack. The email is only used
 * after the visitor clicks the link we send (double opt-in), and every email
 * carries the unsubscribe link built from unsubscribeToken.
 */
#[ORM\Entity(repositoryClass: LeadSubscriberRepository::class)]
#[ORM\Table(name: 'lead_subscriber')]
#[ORM\Index(name: 'idx_lead_subscriber_next_follow_up', columns: ['next_follow_up_at'])]
class LeadSubscriber
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $confirmationToken;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $unsubscribeToken;

    /**
     * Code of the PAU pack shown on the page where the visitor signed up, so the
     * follow-up emails can offer that pack and not a random one.
     */
    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    private ?string $sourcePackCode = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $consentAt;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $consentIp = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $confirmationSentAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $unsubscribedAt = null;

    /** Number of follow-up emails already sent. */
    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private int $followUpStep = 0;

    /** When the next follow-up is due; null when none is planned. */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $nextFollowUpAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastFollowUpAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $email, DateTimeImmutable $now)
    {
        $this->email = $email;
        $this->confirmationToken = bin2hex(random_bytes(32));
        $this->unsubscribeToken = bin2hex(random_bytes(32));
        $this->consentAt = $now;
        $this->createdAt = $now;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

    public function getUnsubscribeToken(): string
    {
        return $this->unsubscribeToken;
    }

    public function getSourcePackCode(): ?string
    {
        return $this->sourcePackCode;
    }

    public function setSourcePackCode(?string $sourcePackCode): self
    {
        $this->sourcePackCode = $sourcePackCode;

        return $this;
    }

    public function getConsentAt(): DateTimeImmutable
    {
        return $this->consentAt;
    }

    public function getConsentIp(): ?string
    {
        return $this->consentIp;
    }

    /**
     * Records a new consent: the first signup, or a signup after an unsubscribe.
     */
    public function giveConsent(DateTimeImmutable $now, ?string $ip): self
    {
        $this->consentAt = $now;
        $this->consentIp = $ip;
        $this->unsubscribedAt = null;

        return $this;
    }

    /**
     * A signup after an unsubscribe: the address must be confirmed again before
     * any follow-up goes out, because anyone can type an address in the form.
     */
    public function resubscribe(DateTimeImmutable $now, ?string $ip): self
    {
        $this->giveConsent($now, $ip);
        $this->confirmedAt = null;
        $this->nextFollowUpAt = null;

        return $this;
    }

    public function getConfirmationSentAt(): ?DateTimeImmutable
    {
        return $this->confirmationSentAt;
    }

    public function markConfirmationSent(DateTimeImmutable $now): self
    {
        $this->confirmationSentAt = $now;

        return $this;
    }

    public function getConfirmedAt(): ?DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmedAt !== null;
    }

    public function confirm(DateTimeImmutable $now): self
    {
        $this->confirmedAt ??= $now;

        return $this;
    }

    public function getUnsubscribedAt(): ?DateTimeImmutable
    {
        return $this->unsubscribedAt;
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribedAt !== null;
    }

    public function unsubscribe(DateTimeImmutable $now): self
    {
        $this->unsubscribedAt ??= $now;
        $this->nextFollowUpAt = null;

        return $this;
    }

    public function getFollowUpStep(): int
    {
        return $this->followUpStep;
    }

    public function getNextFollowUpAt(): ?DateTimeImmutable
    {
        return $this->nextFollowUpAt;
    }

    public function scheduleNextFollowUp(?DateTimeImmutable $nextFollowUpAt): self
    {
        $this->nextFollowUpAt = $nextFollowUpAt;

        return $this;
    }

    public function getLastFollowUpAt(): ?DateTimeImmutable
    {
        return $this->lastFollowUpAt;
    }

    public function markFollowUpSent(DateTimeImmutable $now): self
    {
        ++$this->followUpStep;
        $this->lastFollowUpAt = $now;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
