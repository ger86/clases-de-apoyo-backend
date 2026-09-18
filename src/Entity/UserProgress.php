<?php

namespace App\Entity;

use App\Model\ProgressKind;
use App\Model\ProgressTarget;
use App\Repository\UserProgressRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * One row per chapter or exam a student marked as done. Rows follow the account, so a
 * student sees the same marks on every phone and a shared phone shows nobody else's.
 */
#[ORM\Entity(repositoryClass: UserProgressRepository::class)]
#[ORM\Table(name: 'user_progress')]
#[ORM\UniqueConstraint(name: 'user_progress_unique_target', columns: ['user_id', 'kind', 'target_id'])]
class UserProgress
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 20, enumType: ProgressKind::class)]
    private ProgressKind $kind;

    #[ORM\Column(type: 'integer')]
    private int $targetId;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $doneAt;

    public function __construct(User $user, ProgressTarget $target, DateTimeImmutable $doneAt)
    {
        $this->user = $user;
        $this->kind = $target->kind;
        $this->targetId = $target->id;
        $this->doneAt = $doneAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTarget(): ProgressTarget
    {
        return new ProgressTarget($this->kind, $this->targetId);
    }

    public function getDoneAt(): DateTimeImmutable
    {
        return $this->doneAt;
    }
}
