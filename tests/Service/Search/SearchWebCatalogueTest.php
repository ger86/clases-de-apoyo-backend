<?php

namespace App\Tests\Service\Search;

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
use App\Model\View\WebSearchResultView;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;
use App\Service\Search\SearchWebCatalogue;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SearchWebCatalogueTest extends TestCase
{
    public function testAnExamIsFoundWithoutAccentsAndLinksToItsPage(): void
    {
        $results = $this->createSearch()->search('matematicas MADRID 2026');

        self::assertCount(1, $results);
        self::assertSame(WebSearchResultView::TYPE_EXAM, $results[0]->type);
        self::assertSame('Matemáticas 2026 modelo', $results[0]->title);
        self::assertSame('Selectividad · Madrid', $results[0]->context);
        self::assertSame('/exam/selectividad/madrid/matematicas/2026-modelo', $results[0]->url);
    }

    public function testEveryWordMustMatch(): void
    {
        self::assertSame([], $this->createSearch()->search('matematicas valencia'));
    }

    public function testAChapterIsFoundByItsDescription(): void
    {
        $results = $this->createSearch()->search('raíces');

        self::assertCount(1, $results);
        self::assertSame(WebSearchResultView::TYPE_CHAPTER, $results[0]->type);
        self::assertSame('Números Reales', $results[0]->title);
        self::assertSame('Operaciones con números reales y raíces', $results[0]->description);
        self::assertSame('/chapter/4o-eso/matematicas/numeros-reales', $results[0]->url);
    }

    public function testNameMatchesComeBeforeDescriptionMatches(): void
    {
        // The exam comes first from the database, but only its description says «reales».
        $results = $this->createSearch()->search('reales');

        self::assertCount(2, $results);
        self::assertSame(WebSearchResultView::TYPE_CHAPTER, $results[0]->type);
        self::assertSame(WebSearchResultView::TYPE_EXAM, $results[1]->type);
    }

    public function testAQueryTooShortFindsNothing(): void
    {
        self::assertSame([], $this->createSearch()->search('m'));
        self::assertSame([], $this->createSearch()->search('   '));
    }

    private function createSearch(): SearchWebCatalogue
    {
        $examRepository = $this->createStub(ExamRepository::class);
        $examRepository->method('findAllForSearchIndex')->willReturn([$this->createExam()]);

        $chapterRepository = $this->createStub(ChapterRepository::class);
        $chapterRepository->method('findAllForSearchIndex')->willReturn([$this->createChapter()]);

        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn (string $name, array $parameters): string => '/' . $name . '/' . implode('/', $parameters)
        );

        return new SearchWebCatalogue($examRepository, $chapterRepository, $urlGenerator, new ArrayAdapter());
    }

    private function createExam(): Exam
    {
        $communityTest = (new CommunityTest())
            ->setCommunity((new Community())->setName('Madrid')->setSlug('madrid'))
            ->setKnowledgeTest((new KnowledgeTest())->setName('Selectividad')->setSlug('selectividad'));

        $courseSubject = (new CourseSubject())
            ->setCourse((new Course())->setName('2º Bachillerato')->setSlug('2o-bachillerato'))
            ->setSubject((new Subject())->setName('Matemáticas')->setSlug('matematicas'));

        $communityTestCourseSubject = (new CommunityTestCourseSubject())
            ->setCommunityTest($communityTest)
            ->setCourseSubject($courseSubject);

        $testYear = (new TestYear())->setYear('2026')->setCommunityTestCourseSubject($communityTestCourseSubject);

        return $this->with(
            (new Exam())->setTestYear($testYear)->setSlug('2026-modelo')->setDescription('<p>Incluye números reales</p>'),
            ['name' => '2026 modelo']
        );
    }

    private function createChapter(): Chapter
    {
        $courseSubject = (new CourseSubject())
            ->setCourse((new Course())->setName('4º E.S.O')->setSlug('4o-eso'))
            ->setSubject((new Subject())->setName('Matemáticas')->setSlug('matematicas'));

        $chapterBlock = (new ChapterBlock())->setName('Álgebra')->setCourseSubject($courseSubject);

        return $this->with(
            (new Chapter())
                ->setName('Números Reales')
                ->setDescription("<p>Operaciones con n&uacute;meros reales&nbsp;</p>\n\n<p>y ra&iacute;ces</p>")
                ->setChapterBlock($chapterBlock),
            ['slug' => 'numeros-reales']
        );
    }

    /**
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
