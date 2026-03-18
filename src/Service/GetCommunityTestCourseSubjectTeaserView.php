<?php

namespace App\Service;

use App\Entity\CommunityTestCourseSubject;
use App\Model\View\CommunityTestCourseSubjectTeaserView;

class GetCommunityTestCourseSubjectTeaserView
{
    public function __construct(private GetCourseSubjectView $getCourseSubjectView)
    {
    }

    public function __invoke(
        CommunityTestCourseSubject $communityTestCourseSubject
    ): CommunityTestCourseSubjectTeaserView {
        return new CommunityTestCourseSubjectTeaserView(
            $communityTestCourseSubject->getId(),
            ($this->getCourseSubjectView)($communityTestCourseSubject->getCourseSubject()),
            $communityTestCourseSubject->getTestYearsIds()
        );
    }
}
