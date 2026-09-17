<?php

namespace App\Tests\Service;

use App\Entity\Chapter;
use App\Entity\ChapterBlock;
use App\Entity\Community;
use App\Entity\CommunityTest;
use App\Entity\CommunityTestCourseSubject;
use App\Entity\Course;
use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\KnowledgeTest;
use App\Entity\Subject;
use App\Entity\TestYear;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;
use App\Service\GetSearchIndexView;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Pins what the app is allowed to put in the phone search: the path down to an exam or a
 * chapter, and nothing that depends on who is asking.
 */
final class GetSearchIndexViewTest extends TestCase
{
    public function testAnExamCarriesThePathTheAppNeedsToOpenIt(): void
    {
        $view = ($this->createGetSearchIndexView([$this->createExam()], []))(null);

        self::assertTrue($view->changed);
        self::assertCount(1, $view->exams);

        $exam = $view->exams[0];
        self::assertSame(602, $exam->examId);
        self::assertSame('2026 modelo', $exam->examName);
        self::assertSame('Selectividad', $exam->knowledgeTestName);
        self::assertSame(1, $exam->communityId);
        self::assertSame('Madrid', $exam->communityName);
        self::assertSame(12, $exam->communityTestCourseSubjectId);
        self::assertSame('Matemáticas', $exam->subjectName);
        self::assertSame(55, $exam->testYearId);
        self::assertSame('2026', $exam->testYear);
    }

    public function testDescriptionsArriveAsPlainText(): void
    {
        $view = ($this->createGetSearchIndexView([], [$this->createChapter()]))(null);

        self::assertSame(
            'Operaciones con números reales y raíces',
            $view->chapters[0]->description
        );
    }

    public function testAnAppThatAlreadyHasTheIndexIsSentNothingToDownload(): void
    {
        $getSearchIndexView = $this->createGetSearchIndexView([$this->createExam()], []);
        $version = ($getSearchIndexView)(null)->version;

        $view = ($getSearchIndexView)($version);

        self::assertFalse($view->changed);
        self::assertSame($version, $view->version);
        self::assertSame([], $view->exams);
        self::assertSame([], $view->chapters);
    }

    public function testAnOlderIndexIsSentTheWholeCatalogue(): void
    {
        $view = ($this->createGetSearchIndexView([$this->createExam()], []))('an-older-version');

        self::assertTrue($view->changed);
        self::assertCount(1, $view->exams);
    }

    /**
     * @param Exam[] $exams
     * @param Chapter[] $chapters
     */
    private function createGetSearchIndexView(array $exams, array $chapters): GetSearchIndexView
    {
        $examRepository = $this->createStub(ExamRepository::class);
        $examRepository->method('findAllForSearchIndex')->willReturn($exams);

        $chapterRepository = $this->createStub(ChapterRepository::class);
        $chapterRepository->method('findAllForSearchIndex')->willReturn($chapters);

        return new GetSearchIndexView($examRepository, $chapterRepository, new ArrayAdapter());
    }

    private function createExam(): Exam
    {
        $communityTest = (new CommunityTest())
            ->setCommunity($this->with((new Community())->setName('Madrid')->setSlug('madrid'), ['id' => 1]))
            ->setKnowledgeTest((new KnowledgeTest())->setName('Selectividad')->setSlug('selectividad'));

        $courseSubject = (new CourseSubject())
            ->setCourse((new Course())->setName('2º Bachillerato')->setSlug('2o-bachillerato'))
            ->setSubject((new Subject())->setName('Matemáticas')->setSlug('matematicas'));

        $communityTestCourseSubject = $this->with(
            (new CommunityTestCourseSubject())
                ->setCommunityTest($communityTest)
                ->setCourseSubject($courseSubject),
            ['id' => 12]
        );

        $testYear = $this->with(
            (new TestYear())->setYear('2026')->setCommunityTestCourseSubject($communityTestCourseSubject),
            ['id' => 55]
        );

        return $this->with(
            (new Exam())->setTestYear($testYear),
            ['id' => 602, 'name' => '2026 modelo']
        );
    }

    private function createChapter(): Chapter
    {
        $courseSubject = $this->with(
            (new CourseSubject())
                ->setCourse($this->with((new Course())->setName('4º E.S.O')->setSlug('4o-eso'), ['id' => 1]))
                ->setSubject((new Subject())->setName('Matemáticas')->setSlug('matematicas')),
            ['id' => 1]
        );

        $chapterBlock = (new ChapterBlock())->setName('Álgebra')->setCourseSubject($courseSubject);

        return $this->with(
            (new Chapter())
                ->setName('Números Reales')
                ->setDescription("<p>Operaciones con n&uacute;meros reales&nbsp;</p>\n\n<p>y ra&iacute;ces</p>")
                ->setChapterBlock($chapterBlock),
            ['id' => 1]
        );
    }

    /**
     * The id is given by the database, and a few fields the admin fills have no setter, so the
     * test writes them straight into the object.
     *
     * @template T of object
     * @param T $entity
     * @param array<string,mixed> $values
     * @return T
     */
    private function with(object $entity, array $values): object
    {
        foreach ($values as $name => $value) {
            (new ReflectionProperty($entity, $name))->setValue($entity, $value);
        }

        return $entity;
    }
}
