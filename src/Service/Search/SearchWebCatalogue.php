<?php

namespace App\Service\Search;

use App\Entity\Chapter;
use App\Entity\Exam;
use App\Model\View\WebSearchResultView;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * The search box of the website. It reads the same exams and chapters as the app search index,
 * keeps them for an hour as plain rows with their web link, and filters those rows in memory.
 * Every word typed must appear in the row; accents and capitals are ignored.
 */
final class SearchWebCatalogue
{
    public const MIN_QUERY_LENGTH = 2;

    private const CACHE_KEY = 'web.search_catalogue';

    private const CACHE_SECONDS = 3600;

    private const MAX_RESULTS = 50;

    private const DESCRIPTION_LENGTH = 200;

    public function __construct(
        private ExamRepository $examRepository,
        private ChapterRepository $chapterRepository,
        private UrlGeneratorInterface $urlGenerator,
        private CacheInterface $cache
    ) {
    }

    /**
     * @return WebSearchResultView[]
     */
    public function search(string $query): array
    {
        $words = $this->words($query);
        if ($words === [] || mb_strlen(trim($query)) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $titleMatches = [];
        $otherMatches = [];
        foreach ($this->catalogue() as $row) {
            if (!$this->containsAll($row['haystack'], $words)) {
                continue;
            }

            // A row whose name already holds every word goes before one that only matches in its description.
            if ($this->containsAll($row['nameHaystack'], $words)) {
                $titleMatches[] = $row['view'];
            } else {
                $otherMatches[] = $row['view'];
            }
        }

        return \array_slice([...$titleMatches, ...$otherMatches], 0, self::MAX_RESULTS);
    }

    /**
     * @return array<array{view: WebSearchResultView, nameHaystack: string, haystack: string}>
     */
    private function catalogue(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_SECONDS);

            return $this->build();
        });
    }

    /**
     * @return array<array{view: WebSearchResultView, nameHaystack: string, haystack: string}>
     */
    private function build(): array
    {
        $rows = [];
        foreach ($this->examRepository->findAllForSearchIndex() as $exam) {
            $view = $this->examView($exam);
            if ($view !== null) {
                $rows[] = $this->row($view);
            }
        }

        foreach ($this->chapterRepository->findAllForSearchIndex() as $chapter) {
            $view = $this->chapterView($chapter);
            if ($view !== null) {
                $rows[] = $this->row($view);
            }
        }

        return $rows;
    }

    /**
     * @return array{view: WebSearchResultView, nameHaystack: string, haystack: string}
     */
    private function row(WebSearchResultView $view): array
    {
        $nameHaystack = $this->normalize($view->title . ' ' . $view->context);

        return [
            'view' => $view,
            'nameHaystack' => $nameHaystack,
            'haystack' => $nameHaystack . ' ' . $this->normalize((string) $view->description),
        ];
    }

    private function examView(Exam $exam): ?WebSearchResultView
    {
        $communityTestCourseSubject = $exam->getTestYear()?->getCommunityTestCourseSubject();
        $communityTest = $communityTestCourseSubject?->getCommunityTest();
        $community = $communityTest?->getCommunity();
        $knowledgeTest = $communityTest?->getKnowledgeTest();
        $subject = $communityTestCourseSubject?->getCourseSubject()?->getSubject();

        if ($community === null || $knowledgeTest === null || $subject === null) {
            return null;
        }

        $examSlug = $exam->getSlug();
        $communitySlug = $community->getSlug();
        $testSlug = $knowledgeTest->getSlug();
        $subjectSlug = $subject->getSlug();

        if ($examSlug === null || $communitySlug === null || $testSlug === null || $subjectSlug === null) {
            return null;
        }

        return new WebSearchResultView(
            WebSearchResultView::TYPE_EXAM,
            $subject->getName() . ' ' . $exam->getName(),
            $knowledgeTest->getName() . ' · ' . $community->getName(),
            $this->toPlainText($exam->getDescription()),
            $this->urlGenerator->generate('exam', [
                'testSlug' => $testSlug,
                'communitySlug' => $communitySlug,
                'courseSubjectSlug' => $subjectSlug,
                'examSlug' => $examSlug,
            ])
        );
    }

    private function chapterView(Chapter $chapter): ?WebSearchResultView
    {
        $chapterBlock = $chapter->getChapterBlock();
        $courseSubject = $chapterBlock?->getCourseSubject();
        $course = $courseSubject?->getCourse();
        $subject = $courseSubject?->getSubject();

        if ($chapterBlock === null || $course === null || $subject === null) {
            return null;
        }

        $chapterSlug = $chapter->getSlug();
        $courseSlug = $course->getSlug();
        $subjectSlug = $subject->getSlug();

        if ($chapterSlug === null || $courseSlug === null || $subjectSlug === null) {
            return null;
        }

        return new WebSearchResultView(
            WebSearchResultView::TYPE_CHAPTER,
            $chapter->getName(),
            $subject->getName() . ' · ' . $course->getName() . ' · ' . $chapterBlock->getName(),
            $this->toPlainText($chapter->getDescription()),
            $this->urlGenerator->generate('chapter', [
                'courseSlug' => $courseSlug,
                'subjectSlug' => $subjectSlug,
                'chapterSlug' => $chapterSlug,
            ])
        );
    }

    /**
     * @return string[]
     */
    private function words(string $query): array
    {
        $words = preg_split('/\s+/u', $this->normalize($query), -1, \PREG_SPLIT_NO_EMPTY);

        return $words === false ? [] : $words;
    }

    /**
     * @param string[] $words
     */
    private function containsAll(string $haystack, array $words): bool
    {
        foreach ($words as $word) {
            if (!str_contains($haystack, $word)) {
                return false;
            }
        }

        return true;
    }

    /** Lower case and no accents, so «matematicas» finds «Matemáticas». */
    private function normalize(string $value): string
    {
        return strtr(mb_strtolower($value), [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ]);
    }

    /** Descriptions are written in the admin WYSIWYG, so they arrive as HTML. */
    private function toPlainText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = html_entity_decode(strip_tags($value), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', $text));

        if ($text === '') {
            return null;
        }

        return mb_strimwidth($text, 0, self::DESCRIPTION_LENGTH, '…');
    }
}
