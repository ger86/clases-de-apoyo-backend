<?php

namespace App\Service;

use App\Entity\Course;
use App\Model\View\CourseTeaserView;

class GetCourseTeaserView
{
    public function __construct()
    {
    }

    public function __invoke(Course $course): CourseTeaserView
    {
        return new CourseTeaserView(
            $course->getId(),
            $course->getName(),
            $course->getDescription(),
            $course->getWeight(),
            $course->getCourseSubjectsIds()
        );
    }
}
