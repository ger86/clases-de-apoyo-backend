<?php

namespace App\Service\Product;

use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\File;

final class MadridMathPackContext
{
    public function supportsExam(Exam $exam): bool
    {
        $communityTestCourseSubject = $exam->getTestYear()->getCommunityTestCourseSubject();
        $communityTest = $communityTestCourseSubject->getCommunityTest();
        $courseSubject = $communityTestCourseSubject->getCourseSubject();

        return $communityTest->getCommunity()->getSlug() === 'madrid'
            && $communityTest->getKnowledgeTest()->getSlug() === 'selectividad'
            && $this->supportsCourseSubject($courseSubject);
    }

    public function supportsFile(File $file): bool
    {
        $exam = $file->getExam();
        if ($exam !== null) {
            return $this->supportsExam($exam);
        }

        $chapter = $file->getChapter();
        if ($chapter === null || $chapter->getChapterBlock() === null) {
            return false;
        }

        return $this->supportsCourseSubject($chapter->getChapterBlock()->getCourseSubject());
    }

    public function supportsCourseSubject(CourseSubject $courseSubject): bool
    {
        return $courseSubject->getCourse()?->getSlug() === '2o-bachillerato'
            && $courseSubject->getSubjectSlug() === 'matematicas';
    }
}
