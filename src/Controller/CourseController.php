<?php

namespace App\Controller;

use App\Repository\{ChapterRepository, CourseRepository, CourseSubjectRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CourseController extends AbstractController
{
    public function course(string $courseSlug, CourseRepository $courseRepository): Response
    {
        $course = $courseRepository->findOneBySlug($courseSlug);
        if ($course === null) {
            throw $this->createNotFoundException('No existe ese curso');
        }
        return $this->render('views/courses/course/course.html.twig', ['course' => $course]);
    }

    public function courseSubject(
        string $courseSlug,
        string $subjectSlug,
        CourseSubjectRepository $courseSubjectRepository
    ): Response {
        $courseSubject = $courseSubjectRepository->findByCourseAndSubjectSlugs([
            'courseSlug' => $courseSlug,
            'subjectSlug' => $subjectSlug
        ]);
        if ($courseSubject === null) {
            throw $this->createNotFoundException('No existe esa asignatura');
        }
        return $this->render('views/courses/course_subject/course_subject.html.twig', [
            'courseSubject' => $courseSubject
        ]);
    }

    public function chapter(
        string $courseSlug,
        string $subjectSlug,
        string $chapterSlug,
        ChapterRepository $chapterRepository
    ): Response {
        $chapter = $chapterRepository->findByCourseAndSubjectAndChapterSlugs([
            'courseSlug' => $courseSlug,
            'subjectSlug' => $subjectSlug,
            'chapterSlug' => $chapterSlug
        ]);
        if ($chapter === null) {
            throw $this->createNotFoundException('No existe ese capítulo');
        }
        return $this->render('views/courses/chapter/chapter.html.twig', ['chapter' => $chapter]);
    }
}
