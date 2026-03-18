<?php

namespace App\Service;

use App\Entity\CourseSubject;
use App\Model\View\CourseSubjectTeaserView;

class GetCourseSubjectTeaserView
{
    public function __construct(private GetSubjectView $getSubjectView)
    {
    }

    public function __invoke(CourseSubject $courseSubject): CourseSubjectTeaserView
    {
        return new CourseSubjectTeaserView(
            $courseSubject->getId(),
            ($this->getSubjectView)($courseSubject->getSubject()),
            $courseSubject->getDescription(),
            $courseSubject->getWeight(),
            $courseSubject->getChapterBlocksIds()
        );
    }
}
