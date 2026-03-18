<?php

namespace App\Model\View;

readonly class CommunityTestView
{

    /**
     * @param CommunityTestCourseSubjectTeaserView[] $communityTestCourseSubjects
     */
    public function __construct(
        public int $id,
        public CommunityView $community,
        public array $communityTestCourseSubjects
    ) {
    }
}
