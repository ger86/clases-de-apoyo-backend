<?php

namespace App\Service\Bookmark;

use App\Entity\File;
use App\Entity\User;
use App\Entity\UserBookmark;
use App\Repository\FileRepository;
use App\Repository\UserBookmarkRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserBookmarkManager
{
    /** A merge comes from one phone, so a bigger list is a bug in the app, not a student. */
    public const MAX_MERGE_IDS = 1000;

    public function __construct(
        private EntityManagerInterface $em,
        private UserBookmarkRepository $userBookmarkRepository,
        private FileRepository $fileRepository
    ) {
    }

    public function isBookmarked(User $user, File $file): bool
    {
        $fileId = $file->getId();

        return $fileId !== null && $this->userBookmarkRepository->findOneByUserAndFileId($user, $fileId) !== null;
    }

    /** Saving twice is fine: the first date is kept. */
    public function add(User $user, int $fileId): void
    {
        $file = $fileId > 0 ? $this->fileRepository->find($fileId) : null;
        if ($file === null) {
            throw new NotFoundHttpException('Ese archivo no existe.');
        }

        if ($this->userBookmarkRepository->findOneByUserAndFileId($user, $fileId) !== null) {
            return;
        }

        $this->em->persist(new UserBookmark($user, $file, new DateTimeImmutable()));

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // A retry or a second tap got there first. The bookmark exists, which is what was asked.
        }
    }

    public function remove(User $user, int $fileId): void
    {
        $bookmark = $this->userBookmarkRepository->findOneByUserAndFileId($user, $fileId);
        if ($bookmark === null) {
            return;
        }

        $this->em->remove($bookmark);
        $this->em->flush();
    }

    /**
     * Adds the files a student saved before having an account. Ids that are malformed or
     * point to a file that no longer exists are skipped: losing one stale bookmark beats
     * refusing the whole batch.
     *
     * @param array<mixed> $fileIds
     * @return int how many bookmarks were added
     */
    public function merge(User $user, array $fileIds): int
    {
        if (\count($fileIds) > self::MAX_MERGE_IDS) {
            throw new InvalidArgumentException('Demasiados guardados en una sola petición.');
        }

        // The unique index would reject a repeated id at flush time and lose the whole batch,
        // so everything already saved, in the table or earlier in this list, is skipped here.
        $seen = [];
        foreach ($this->userBookmarkRepository->findByUser($user) as $bookmark) {
            $seen[(int) $bookmark->getFile()->getId()] = true;
        }

        $wanted = [];
        foreach ($fileIds as $fileId) {
            if (!\is_int($fileId) || $fileId <= 0 || isset($seen[$fileId])) {
                continue;
            }

            $wanted[$fileId] = true;
        }

        if ($wanted === []) {
            return 0;
        }

        $added = 0;
        $now = new DateTimeImmutable();
        foreach ($this->fileRepository->findBy(['id' => array_keys($wanted)]) as $file) {
            $this->em->persist(new UserBookmark($user, $file, $now));
            ++$added;
        }

        if ($added > 0) {
            $this->em->flush();
        }

        return $added;
    }
}
