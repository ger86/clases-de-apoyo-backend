<?php

namespace App\Tests\Service\Progress;

use App\Entity\User;
use App\Entity\UserProgress;
use App\Model\ProgressKind;
use App\Entity\Chapter;
use App\Entity\Exam;
use App\Model\ProgressTarget;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;
use App\Repository\UserProgressRepository;
use App\Service\Progress\ProgressTargetExists;
use App\Service\Progress\UserProgressManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserProgressManagerTest extends TestCase
{
    public function testKeyRoundTrip(): void
    {
        $target = ProgressTarget::fromKey('exam:12');

        self::assertSame(ProgressKind::Exam, $target->kind);
        self::assertSame(12, $target->id);
        self::assertSame('exam:12', $target->key());
    }

    public function testMalformedKeysAreRejected(): void
    {
        foreach (['exam', 'file:3', 'chapter:abc', 'chapter:0', 'chapter:-1', ''] as $key) {
            try {
                ProgressTarget::fromKey($key);
                self::fail(\sprintf('"%s" should not parse', $key));
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testMarkingUnknownContentIsRefused(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');

        $manager = $this->createManager($em, exists: false, existing: null);

        $this->expectException(NotFoundHttpException::class);
        $manager->markDone(new User(), new ProgressTarget(ProgressKind::Chapter, 5));
    }

    public function testMarkingTwiceKeepsOneRow(): void
    {
        $user = new User();
        $existing = new UserProgress($user, new ProgressTarget(ProgressKind::Chapter, 5), new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $this->createManager($em, exists: true, existing: $existing)
            ->markDone($user, new ProgressTarget(ProgressKind::Chapter, 5));
    }

    public function testMergeSkipsBadKeysAndCountsTheRest(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::exactly(2))->method('persist')->with(self::isInstanceOf(UserProgress::class));
        $em->expects(self::once())->method('flush');

        $added = $this->createManager($em, exists: true, existing: null)
            ->merge(new User(), ['chapter:1', 'nonsense', 42, 'exam:2', 'exam:x']);

        self::assertSame(2, $added);
    }

    public function testMergeSkipsKeysRepeatedInTheBatchOrAlreadyMarked(): void
    {
        $user = new User();
        $existing = new UserProgress($user, new ProgressTarget(ProgressKind::Exam, 2), new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');

        $added = $this->createManager($em, exists: true, existing: null, alreadyMarked: [$existing])
            ->merge($user, ['chapter:1', 'chapter:1', 'exam:2']);

        self::assertSame(1, $added);
    }

    public function testMarkingIsIdempotentWhenAnotherRequestWonTheRace(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');
        $em->method('flush')->willThrowException(
            $this->createStub(UniqueConstraintViolationException::class)
        );

        $this->createManager($em, exists: true, existing: null)
            ->markDone(new User(), new ProgressTarget(ProgressKind::Chapter, 5));

        self::assertTrue(true);
    }

    public function testMergeRefusesAnAbsurdBatch(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $manager = $this->createManager($em, exists: true, existing: null);

        $this->expectException(InvalidArgumentException::class);
        $manager->merge(new User(), array_fill(0, UserProgressManager::MAX_MERGE_KEYS + 1, 'chapter:1'));
    }

    /**
     * @param UserProgress[] $alreadyMarked
     */
    private function createManager(
        EntityManagerInterface $em,
        bool $exists,
        ?UserProgress $existing,
        array $alreadyMarked = []
    ): UserProgressManager {
        $repository = $this->createStub(UserProgressRepository::class);
        $repository->method('findOneByUserAndTarget')->willReturn($existing);
        $repository->method('findByUser')->willReturn($alreadyMarked);

        $chapterRepository = $this->createStub(ChapterRepository::class);
        $chapterRepository->method('find')->willReturn($exists ? new Chapter() : null);
        $examRepository = $this->createStub(ExamRepository::class);
        $examRepository->method('find')->willReturn($exists ? new Exam() : null);

        return new UserProgressManager(
            $em,
            $repository,
            new ProgressTargetExists($chapterRepository, $examRepository)
        );
    }
}
