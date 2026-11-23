<?php

namespace App\Service\Bookmark;

use App\Entity\File;
use App\Entity\User;
use App\Entity\UserBookmark;
use App\Repository\FileRepository;
use App\Repository\UserBookmarkRepository;
use DateTimeImmutable;
use Exception;
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
     * Adds the files a student saved before having an account. Each item is a file id, or
     * {"id": 12, "bookmarkedAt": "2026-01-31T10:00:00Z"} so the list keeps its order. Items
     * that are malformed or point to a file that no longer exists are skipped: losing one
     * stale bookmark beats refusing the whole batch. A missing, broken or future date
     * becomes now.
     *
     * @param array<mixed> $files
     * @return int how many bookmarks were added
     */
    public function merge(User $user, array $files): int
    {
        if (\count($files) > self::MAX_MERGE_IDS) {
            throw new InvalidArgumentException('Demasiados guardados en una sola petición.');
        }

        // The unique index would reject a repeated id at flush time and lose the whole batch,
        // so everything already saved, in the table or earlier in this list, is skipped here.
        $seen = [];
        foreach ($this->userBookmarkRepository->findByUser($user) as $bookmark) {
            $seen[(int) $bookmark->getFile()->getId()] = true;
        }

        $now = new DateTimeImmutable();
        /** @var array<int, DateTimeImmutable> $wanted */
        $wanted = [];
        foreach ($files as $item) {
            $fileId = \is_array($item) ? ($item['id'] ?? null) : $item;
            if (!\is_int($fileId) || $fileId <= 0 || isset($seen[$fileId]) || isset($wanted[$fileId])) {
                continue;
            }

            $wanted[$fileId] = $this->readDate(\is_array($item) ? ($item['bookmarkedAt'] ?? null) : null, $now);
        }

        if ($wanted === []) {
            return 0;
        }

        $added = 0;
        foreach ($this->fileRepository->findBy(['id' => array_keys($wanted)]) as $file) {
            $this->em->persist(new UserBookmark($user, $file, $wanted[(int) $file->getId()]));
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
