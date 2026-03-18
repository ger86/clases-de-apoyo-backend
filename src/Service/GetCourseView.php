<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\CourseSubject;
use App\Model\View\CourseView;

class GetCourseView
{
    public function __construct(private GetCourseSubjectView $getCourseSubjectView)
    {
    }

    public function __invoke(Course $course): CourseView
    {
        return new CourseView(
            $course->getId(),
            $course->getName(),
            $course->getDescription(),
            $course->getWeight(),
            array_map(
                fn (CourseSubject $cs) => ($this->getCourseSubjectView)($cs),
                $course->getCourseSubjects()->toArray()
            )
        );
    }
}
