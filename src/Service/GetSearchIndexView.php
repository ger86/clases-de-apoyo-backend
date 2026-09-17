<?php

namespace App\Service;

use App\Entity\Chapter;
use App\Entity\Exam;
use App\Model\View\SearchIndexChapterView;
use App\Model\View\SearchIndexExamView;
use App\Model\View\SearchIndexView;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Builds the catalogue the app hands to the phone search, Spotlight on iOS.
 *
 * It carries the names above each exam and chapter and the text that says what they are about,
 * never a download link and never a locked flag. Both of those depend on the account, while a
 * phone index is written once for everybody, so the app opens the exam or the chapter screen and
 * this API decides there, as it already does, who may read a file.
 */
final class GetSearchIndexView
{
    private const CACHE_KEY = 'api.search_index';

    private const CACHE_SECONDS = 3600;

    /** Long enough for the topics of an exam, short enough to keep the whole answer small. */
    private const DESCRIPTION_LENGTH = 600;

    public function __construct(
        private ExamRepository $examRepository,
        private ChapterRepository $chapterRepository,
        private CacheInterface $cache
    ) {
    }

    public function __invoke(?string $since): SearchIndexView
    {
        /** @var array{version: string, exams: SearchIndexExamView[], chapters: SearchIndexChapterView[]} $index */
        $index = $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_SECONDS);

            return $this->build();
        });

        if ($since === $index['version']) {
            return new SearchIndexView($index['version'], false, [], []);
        }

        return new SearchIndexView($index['version'], true, $index['exams'], $index['chapters']);
    }

    /**
     * @return array{version: string, exams: SearchIndexExamView[], chapters: SearchIndexChapterView[]}
     */
    private function build(): array
    {
        $exams = [];
        foreach ($this->examRepository->findAllForSearchIndex() as $exam) {
            $view = $this->examView($exam);
            if ($view !== null) {
                $exams[] = $view;
            }
        }

        $chapters = [];
        foreach ($this->chapterRepository->findAllForSearchIndex() as $chapter) {
            $view = $this->chapterView($chapter);
            if ($view !== null) {
                $chapters[] = $view;
            }
        }

        return [
            // The whole answer hashed, so renaming a community changes the version as surely as
            // adding an exam does.
            'version' => sha1((string) json_encode([$exams, $chapters])),
            'exams' => $exams,
            'chapters' => $chapters,
        ];
    }

    /**
     * An item with a hole anywhere above it is left out. The app rebuilds the path down to the
     * exam from these names and ids, and half a path leads to a screen with nothing to ask for.
     */
    private function examView(Exam $exam): ?SearchIndexExamView
    {
        $testYear = $exam->getTestYear();
        $communityTestCourseSubject = $testYear?->getCommunityTestCourseSubject();
        $communityTest = $communityTestCourseSubject?->getCommunityTest();
        $community = $communityTest?->getCommunity();
        $knowledgeTest = $communityTest?->getKnowledgeTest();
        $subject = $communityTestCourseSubject?->getCourseSubject()?->getSubject();

        if (
            $testYear === null
            || $communityTestCourseSubject === null
            || $community === null
            || $knowledgeTest === null
            || $subject === null
        ) {
            return null;
        }

        $examId = $exam->getId();
        $communityId = $community->getId();
        $communityTestCourseSubjectId = $communityTestCourseSubject->getId();
        $testYearId = $testYear->getId();

        if ($examId === null || $communityId === null || $communityTestCourseSubjectId === null || $testYearId === null) {
            return null;
        }

        return new SearchIndexExamView(
            $examId,
            $exam->getName(),
            $this->toPlainText($exam->getDescription()),
            $knowledgeTest->getName(),
            $communityId,
            $community->getName(),
            $communityTestCourseSubjectId,
            $subject->getName(),
            $testYearId,
            $testYear->getYear()
        );
    }

    private function chapterView(Chapter $chapter): ?SearchIndexChapterView
    {
        $chapterBlock = $chapter->getChapterBlock();
        $courseSubject = $chapterBlock?->getCourseSubject();
        $course = $courseSubject?->getCourse();
        $subject = $courseSubject?->getSubject();

        if ($chapterBlock === null || $courseSubject === null || $course === null || $subject === null) {
            return null;
        }

        $chapterId = $chapter->getId();
        $courseId = $course->getId();
        $courseSubjectId = $courseSubject->getId();

        if ($chapterId === null || $courseId === null || $courseSubjectId === null) {
            return null;
        }

        return new SearchIndexChapterView(
            $chapterId,
            $chapter->getName(),
            $this->toPlainText($chapter->getDescription()),
            $courseId,
            $course->getName(),
            $courseSubjectId,
            $subject->getName(),
            $chapterBlock->getName()
        );
    }

    /**
     * Descriptions are written in the admin WYSIWYG, so they arrive as HTML. A phone index shows
     * them as one line of text and matches words in them, and neither wants tags or entities.
     */
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
