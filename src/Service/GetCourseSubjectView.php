<?php

namespace App\Service;

use App\Entity\ChapterBlock;
use App\Entity\CourseSubject;
use App\Model\View\CourseSubjectView;

class GetCourseSubjectView
{
    public function __construct(private GetSubjectView $getSubjectView, private GetChapterBlockView $getChapterBlockView)
    {
    }

    public function __invoke(CourseSubject $courseSubject): CourseSubjectView
    {
        return new CourseSubjectView(
            $courseSubject->getId(),
            ($this->getSubjectView)($courseSubject->getSubject()),
            $courseSubject->getDescription(),
            $courseSubject->getWeight(),
            array_map(
                fn (ChapterBlock $chapterBlock) => ($this->getChapterBlockView)($chapterBlock),
                $courseSubject->getChapterBlocks()->toArray()
            ),
        );
    }
}
