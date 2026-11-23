<?php

namespace App\Tests\Service\Bookmark;

use App\Entity\File;
use App\Entity\User;
use App\Entity\UserBookmark;
use App\Repository\FileRepository;
use App\Repository\UserBookmarkRepository;
use App\Service\Bookmark\UserBookmarkManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserBookmarkManagerTest extends TestCase
{
    public function testSavingAnUnknownFileIsRefused(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');

        $manager = $this->createManager($em, files: [], existing: null);

        $this->expectException(NotFoundHttpException::class);
        $manager->add(new User(), 5);
    }

    public function testSavingTwiceKeepsOneRow(): void
    {
        $user = new User();
        $file = $this->createFile(5);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $this->createManager($em, files: [$file], existing: new UserBookmark($user, $file, new \DateTimeImmutable()))
            ->add($user, 5);
    }

    public function testSavingIsIdempotentWhenAnotherRequestWonTheRace(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(UserBookmark::class));
        $em->method('flush')->willThrowException(
            $this->createStub(UniqueConstraintViolationException::class)
        );

        $this->createManager($em, files: [$this->createFile(5)], existing: null)
            ->add(new User(), 5);

        self::assertTrue(true);
    }

    public function testRemovingAFileThatIsNotSavedDoesNothing(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('remove');
        $em->expects(self::never())->method('flush');

        $this->createManager($em, files: [], existing: null)->remove(new User(), 5);
    }

    public function testRemovingDeletesTheRow(): void
    {
        $user = new User();
        $bookmark = new UserBookmark($user, $this->createFile(5), new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('remove')->with($bookmark);
        $em->expects(self::once())->method('flush');

        $this->createManager($em, files: [], existing: $bookmark)->remove($user, 5);
    }

    public function testMergeSkipsBadIdsAndFilesThatNoLongerExist(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::exactly(2))->method('persist')->with(self::isInstanceOf(UserBookmark::class));
        $em->expects(self::once())->method('flush');

        // Only 1 and 2 still exist; 99 was deleted since the guest saved it.
        $added = $this->createManager($em, files: [$this->createFile(1), $this->createFile(2)], existing: null)
            ->merge(new User(), [1, 'nonsense', 2, '3', 0, -4, 99, null]);

        self::assertSame(2, $added);
    }

    public function testMergeSkipsIdsRepeatedInTheBatchOrAlreadySaved(): void
    {
        $user = new User();
        $alreadySaved = new UserBookmark($user, $this->createFile(2), new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');

        $added = $this->createManager(
            $em,
            files: [$this->createFile(1), $this->createFile(2)],
            existing: null,
            alreadySaved: [$alreadySaved]
        )->merge($user, [1, 1, 2]);

        self::assertSame(1, $added);
    }

    public function testMergeWithNothingNewWritesNothing(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $added = $this->createManager($em, files: [], existing: null)->merge(new User(), []);

        self::assertSame(0, $added);
    }

    public function testMergeRefusesAnAbsurdBatch(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $manager = $this->createManager($em, files: [], existing: null);

        $this->expectException(InvalidArgumentException::class);
        $manager->merge(new User(), array_fill(0, UserBookmarkManager::MAX_MERGE_IDS + 1, 1));
    }

    private function createFile(int $id): File
    {
        $file = new File();
        (new ReflectionProperty(File::class, 'id'))->setValue($file, $id);

        return $file;
    }

    /**
     * @param File[]         $files        the files that exist
     * @param UserBookmark[] $alreadySaved
     */
    private function createManager(
        EntityManagerInterface $em,
        array $files,
        ?UserBookmark $existing,
        array $alreadySaved = []
    ): UserBookmarkManager {
        $byId = [];
        foreach ($files as $file) {
            $byId[(int) $file->getId()] = $file;
        }

        $repository = $this->createStub(UserBookmarkRepository::class);
        $repository->method('findOneByUserAndFileId')->willReturn($existing);
        $repository->method('findByUser')->willReturn($alreadySaved);

        $fileRepository = $this->createStub(FileRepository::class);
        $fileRepository->method('find')->willReturnCallback(
            static fn (mixed $id): ?File => $byId[$id] ?? null
        );
        $fileRepository->method('findBy')->willReturnCallback(
            static fn (array $criteria): array => array_values(
                array_intersect_key($byId, array_flip($criteria['id']))
            )
        );

        return new UserBookmarkManager($em, $repository, $fileRepository);
    }
}
