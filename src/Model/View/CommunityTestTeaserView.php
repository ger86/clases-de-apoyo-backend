<?php

namespace App\Model\View;

readonly class CommunityTestTeaserView
{

    /**
     * @param int[] $communityTestCourseSubjects
     */
    public function __construct(
        public int $id,
        public CommunityView $community,
        public array $communityTestCourseSubjects
    ) {
    }
}
