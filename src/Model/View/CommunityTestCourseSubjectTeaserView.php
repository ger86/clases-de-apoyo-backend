<?php

namespace App\Model\View;

readonly class CommunityTestCourseSubjectTeaserView
{

    /**
     * @param int[] $testYears
     */
    public function __construct(
        public int $id,
        public CourseSubjectView $courseSubject,
        public array $testYears
    ) {
    }
}
