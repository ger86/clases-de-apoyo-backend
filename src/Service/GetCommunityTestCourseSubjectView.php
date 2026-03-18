<?php

namespace App\Service;

use App\Entity\{CommunityTestCourseSubject, TestYear};
use App\Model\View\CommunityTestCourseSubjectView;

class GetCommunityTestCourseSubjectView
{
    public function __construct(
        private GetCourseSubjectView $getCourseSubjectView,
        private GetTestYearTeaserView $getTestYearTeaserView
    ) {
    }

    public function __invoke(
        CommunityTestCourseSubject $communityTestCourseSubject
    ): CommunityTestCourseSubjectView {
        return new CommunityTestCourseSubjectView(
            $communityTestCourseSubject->getId(),
            ($this->getCourseSubjectView)($communityTestCourseSubject->getCourseSubject()),
            array_map(
                fn (TestYear $testYear) => ($this->getTestYearTeaserView)($testYear),
                $communityTestCourseSubject->getTestYears()->toArray()
            )
        );
    }
}
