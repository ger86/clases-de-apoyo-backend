<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\CourseSubject;
use App\Entity\Chapter;
use App\Entity\KnowledgeTest;
use App\Model\Breadcrumb;
use App\Entity\CommunityTest;
use App\Entity\CommunityTestCourseSubject;
use App\Entity\Exam;
use App\Entity\Article;
use App\Entity\Book;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbService
{

    public function __construct(private EntityManagerInterface $em, private UrlGeneratorInterface $router)
    {
    }

    public function getBreadcrumb(mixed $element = null): array
    {
        $breadcrumb = [];
        if ($element instanceof Course) {
            $breadcrumb = $this->getCourseBreadcrumb($element);
        } else if ($element instanceof CourseSubject) {
            $breadcrumb = $this->getCourseSubjectBreadcrumb($element);
        } else if ($element instanceof Chapter) {
            $breadcrumb = $this->getChapterBreadcrumb($element);
        } else if ($element instanceof KnowledgeTest) {
            $breadcrumb = $this->getTestBreadcrumb($element);
        } else if ($element instanceof CommunityTest) {
            $breadcrumb = $this->getCommunityTestBreadcrumb($element);
        } else if ($element instanceof CommunityTestCourseSubject) {
            $breadcrumb = $this->getCommunityTestCourseSubjectBreadcrumb($element);
        } else if ($element instanceof Exam) {
            $breadcrumb = $this->getExamBreadcrumb($element);
        } else if ($element instanceof Article) {
            $breadcrumb = $this->getArticleBreadcrumb($element);
        } else if ($element instanceof Book) {
            $breadcrumb = $this->getBookBreadcrumb($element);
        } else if ($element instanceof File) {
            $breadcrumb = $this->getFileBreadcrumb($element);
        }
        return $breadcrumb;
    }

    private function getHomeBreadcrumb(): Breadcrumb
    {
        $url = $this->router->generate('home');
        $b = new Breadcrumb('Inicio', $url);
        return $b;
    }

    private function getCourseBreadcrumb(Course $course, bool $withCourseLink = false): array
    {
        $url = $withCourseLink ? $this->router->generate(
            'course',
            [
                'courseSlug' => $course->getSlug(),
            ]
        ) : null;
        $courseBreadcrumb = new Breadcrumb('Apuntes de ' . $course->getName(), $url);
        return [
            $this->getHomeBreadcrumb(),
            $courseBreadcrumb
        ];
    }

    private function getCourseSubjectBreadcrumb(CourseSubject $courseSubject, bool $withCourseLink = false): array
    {
        $url = $withCourseLink ? $this->router->generate(
            'course_subject',
            [
                'courseSlug' => $courseSubject->getCourse()->getSlug(),
                'subjectSlug' => $courseSubject->getSubjectSlug(),
            ]
        ) : null;
        $courseSubjectBreadcrumb = new Breadcrumb($courseSubject->getSubjectName(), $url);
        $courseBreacrumb = $this->getCourseBreadcrumb($courseSubject->getCourse(), true);
        $courseBreacrumb[] = $courseSubjectBreadcrumb;
        return $courseBreacrumb;
    }

    private function getChapterBreadcrumb(Chapter $chapter, bool $withLinkInChapter = false): array
    {
        $courseSubject = $chapter->getChapterBlock()->getCourseSubject();
        $url = $withLinkInChapter ? $this->router->generate(
            'chapter',
            [
                'courseSlug' => $courseSubject->getCourse()->getSlug(),
                'subjectSlug' => $courseSubject->getSubjectSlug(),
                'chapterSlug' => $chapter->getSlug(),
            ]
        ) : null;
        $chapterBreadcrumb = new Breadcrumb($chapter->getName(), $url);
        $courseSubjectBreacrumb = $this->getCourseSubjectBreadcrumb($courseSubject, true);
        $courseSubjectBreacrumb[] = $chapterBreadcrumb;
        return $courseSubjectBreacrumb;
    }

    private function getFileBreadcrumb(File $file): array
    {
        $chapter = $file->getChapter();
        $exam = $file->getExam();
        if ($chapter !== null) {
            $breadcrumb = $this->getChapterBreadcrumb($chapter, true);
            $breadcrumb[] = new Breadcrumb($file->getName(), null);
            return $breadcrumb;
        } else if ($exam !== null) {
            $breadcrumb = $this->getExamBreadcrumb($exam, true);
            $breadcrumb[] = new Breadcrumb($file->getName(), null);
            return $breadcrumb;
        }
        return [];
    }


    private function getTestBreadcrumb(KnowledgeTest $test, bool $withTestLink = false): array
    {
        $url = $withTestLink ? $this->router->generate(
            'knowledge_test',
            [
                'testSlug' => $test->getSlug(),
            ]
        ) : null;
        $testBreadcrumb = new Breadcrumb('Exámenes de ' . strtolower($test->getName()), $url);
        return [
            $this->getHomeBreadcrumb(),
            $testBreadcrumb
        ];
    }

    private function getCommunityTestBreadcrumb(CommunityTest $ct, bool $withFinalLink = false): array
    {
        $url = $withFinalLink ? $this->router->generate(
            'community_test',
            [
                'testSlug' => $ct->getKnowledgeTest()->getSlug(),
                'communitySlug' => $ct->getCommunity()->getSlug(),
            ]
        ) : null;
        $ctBreadcrumb = new Breadcrumb($ct->getCommunity()->getName(), $url);
        $breadcrumb = $this->getTestBreadcrumb($ct->getKnowledgeTest(), true);
        $breadcrumb[] = $ctBreadcrumb;
        return $breadcrumb;
    }

    private function getCommunityTestCourseSubjectBreadcrumb(
        CommunityTestCourseSubject $courseSubject,
        bool $withFinalLink = false
    ): array {
        $communityTest = $courseSubject->getCommunityTest();
        $url = $withFinalLink ? $this->router->generate(
            'community_test_course_subject',
            [
                'testSlug' => $communityTest->getKnowledgeTest()->getSlug(),
                'communitySlug' => $communityTest->getCommunity()->getSlug(),
                'courseSubjectSlug' => $courseSubject->getCourseSubject()->getSubjectSlug()
            ]
        ) : null;
        $ctBreadcrumb = new Breadcrumb($courseSubject->getCourseSubject()->getSubjectName(), $url);
        $breadcrumb = $this->getCommunityTestBreadcrumb($communityTest, true);
        $breadcrumb[] = $ctBreadcrumb;
        return $breadcrumb;
    }

    private function getExamBreadcrumb(
        Exam $exam,
        bool $withFinalLink = false
    ): array {
        $courseSubject = $exam->getTestYear()->getCommunityTestCourseSubject();
        $communityTest = $exam->getTestYear()->getCommunityTestCourseSubject()->getCommunityTest();
        $url = $withFinalLink ? $this->router->generate(
            'exam',
            [
                'testSlug' => $communityTest->getKnowledgeTest()->getSlug(),
                'communitySlug' => $communityTest->getCommunity()->getSlug(),
                'courseSubjectSlug' => $courseSubject->getCourseSubject()->getSubjectSlug(),
                'examSlug' => $exam->getSlug()
            ]
        ) : null;

        $examBreadcrumb = new Breadcrumb($exam->getName(), $url);
        $breadcrumb = $this->getCommunityTestCourseSubjectBreadcrumb($courseSubject, true);
        $breadcrumb[] = $examBreadcrumb;
        return $breadcrumb;
    }

    private function getArticleBreadcrumb(Article $article): array
    {
        $blogIndexBreadcrumb = new Breadcrumb('Blog', $this->router->generate('blog_index'));
        $articleBreadcrumb = new Breadcrumb($article->getTitle(), null);
        return [
            $this->getHomeBreadcrumb(),
            $blogIndexBreadcrumb,
            $articleBreadcrumb
        ];
    }

    private function getBookBreadcrumb(Book $book): array
    {
        $shopIndexBreadcrumb = new Breadcrumb('Tienda', null);
        $bookBreadcrumb = new Breadcrumb($book->getTitle(), null);
        return [
            $this->getHomeBreadcrumb(),
            $shopIndexBreadcrumb,
            $bookBreadcrumb
        ];
    }
}
