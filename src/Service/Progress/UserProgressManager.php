<?php

namespace App\Service\Progress;

use App\Entity\User;
use App\Entity\UserProgress;
use App\Model\ProgressTarget;
use App\Repository\UserProgressRepository;
use DateTimeImmutable;
use Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserProgressManager
{
    /** A merge comes from one phone, so a bigger list is a bug in the app, not a student. */
    public const MAX_MERGE_KEYS = 1000;

    public function __construct(
        private EntityManagerInterface $em,
        private UserProgressRepository $userProgressRepository,
        private ProgressTargetExists $targetExists
    ) {
    }

    /** Marking twice is fine: the first date is kept. */
    public function markDone(User $user, ProgressTarget $target, ?DateTimeImmutable $doneAt = null): void
    {
        if (!($this->targetExists)($target)) {
            throw new NotFoundHttpException('Ese contenido no existe.');
        }

        if ($this->userProgressRepository->findOneByUserAndTarget($user, $target) !== null) {
            return;
        }

        $this->em->persist(new UserProgress($user, $target, $doneAt ?? new DateTimeImmutable()));

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // A retry or a second tap got there first. The mark exists, which is what was asked.
        }
    }

    public function unmark(User $user, ProgressTarget $target): void
    {
        $progress = $this->userProgressRepository->findOneByUserAndTarget($user, $target);
        if ($progress === null) {
            return;
        }

        $this->em->remove($progress);
        $this->em->flush();
    }

    /**
     * Adds the marks a student made before having an account. Each item is a key such as
     * "chapter:12" (what app 9.2.0 sends), or {"key": "chapter:12", "doneAt": "..."} so the
     * marks keep their date. Items that are malformed or point to nothing are skipped:
     * losing one stale mark beats refusing the whole batch. A missing, broken or future
     * date becomes now.
     *
     * @param array<mixed> $items
     * @return int how many marks were added
     */
    public function merge(User $user, array $items): int
    {
        if (\count($items) > self::MAX_MERGE_KEYS) {
            throw new InvalidArgumentException('Demasiadas marcas en una sola petición.');
        }

        // The unique index would reject a repeated key at flush time and lose the whole batch,
        // so everything already marked, in the table or earlier in this list, is skipped here.
        $seen = [];
        foreach ($this->userProgressRepository->findByUser($user) as $progress) {
            $seen[$progress->getTarget()->key()] = true;
        }

        $added = 0;
        $now = new DateTimeImmutable();
        foreach ($items as $item) {
            $key = \is_array($item) ? ($item['key'] ?? null) : $item;
            if (!\is_string($key)) {
                continue;
            }

            try {
                $target = ProgressTarget::fromKey($key);
            } catch (InvalidArgumentException) {
                continue;
            }

            if (isset($seen[$target->key()]) || !($this->targetExists)($target)) {
                continue;
            }

            $seen[$target->key()] = true;
            $doneAt = $this->readDate(\is_array($item) ? ($item['doneAt'] ?? null) : null, $now);
            $this->em->persist(new UserProgress($user, $target, $doneAt));
            ++$added;
        }

        if ($added > 0) {
            $this->em->flush();
        }

        return $added;
    }

    private function readDate(mixed $value, DateTimeImmutable $now): DateTimeImmutable
    {
        if (!\is_string($value) || $value === '') {
            return $now;
        }

        try {
            $date = new DateTimeImmutable($value);
        } catch (Exception) {
            return $now;
        }

        return $date > $now ? $now : $date;
    }
}
