<?php

namespace App\Model\View;

readonly class CommunityTestCourseSubjectView
{

    /**
     * @param TestYearTeaserView[] $testYears
     */
    public function __construct(
        public int $id,
        public CourseSubjectView $courseSubject,
        public array $testYears
    ) {
    }
}
