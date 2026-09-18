<?php

namespace App\Service\Progress;

use App\Entity\User;
use App\Entity\UserProgress;
use App\Model\ProgressTarget;
use App\Repository\UserProgressRepository;
use DateTimeImmutable;
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
     * Adds the marks a student made before having an account. Keys that are malformed or
     * point to nothing are skipped: losing one stale mark beats refusing the whole batch.
     *
     * @param array<mixed> $keys
     * @return int how many marks were added
     */
    public function merge(User $user, array $keys): int
    {
        if (\count($keys) > self::MAX_MERGE_KEYS) {
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
        foreach ($keys as $key) {
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
            $this->em->persist(new UserProgress($user, $target, $now));
            ++$added;
        }

        if ($added > 0) {
            $this->em->flush();
        }

        return $added;
    }
}
