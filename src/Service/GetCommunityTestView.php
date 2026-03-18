<?php

namespace App\Service;

use App\Entity\{CommunityTest, CommunityTestCourseSubject};
use App\Model\View\CommunityTestView;

class GetCommunityTestView
{
    public function __construct(
        private GetCommunityView $getCommunityView,
        private GetCommunityTestCourseSubjectView $getCommunityTestCourseSubjectView
    ) {
    }

    public function __invoke(CommunityTest $communityTest): CommunityTestView
    {
        return new CommunityTestView(
            $communityTest->getId(),
            ($this->getCommunityView)($communityTest->getCommunity()),
            array_map(
                fn (CommunityTestCourseSubject $communityTestCourseSubject) => ($this->getCommunityTestCourseSubjectView)($communityTestCourseSubject),
                $communityTest->getCommunityTestCourseSubjects()->toArray()
            )
        );
    }
}
