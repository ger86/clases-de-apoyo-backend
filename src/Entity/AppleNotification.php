<?php

namespace App\Entity;

use App\Repository\AppleNotificationRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * One row per App Store Server Notification. It gives idempotency (Apple retries)
 * and a trail to look at when a subscription does not behave as expected.
 */
#[ORM\Entity(repositoryClass: AppleNotificationRepository::class)]
#[ORM\Table(name: 'apple_notification')]
class AppleNotification
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $notificationUuid;

    #[ORM\Column(type: 'string', length: 60)]
    private string $notificationType;

    #[ORM\Column(type: 'string', length: 60, nullable: true)]
    private ?string $subtype = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $originalTransactionId = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $environment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $receivedAt;

    public function __construct(
        string $notificationUuid,
        string $notificationType,
        ?string $subtype,
        ?string $originalTransactionId,
        ?string $environment
    ) {
        $this->notificationUuid = $notificationUuid;
        $this->notificationType = $notificationType;
        $this->subtype = $subtype;
        $this->originalTransactionId = $originalTransactionId;
        $this->environment = $environment;
        $this->receivedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotificationUuid(): string
    {
        return $this->notificationUuid;
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function getSubtype(): ?string
    {
        return $this->subtype;
    }

    public function getOriginalTransactionId(): ?string
    {
        return $this->originalTransactionId;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getReceivedAt(): DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function __toString()
    {
        return $this->notificationType . ' ' . $this->notificationUuid;
    }
}
