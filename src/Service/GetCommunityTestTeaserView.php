<?php

namespace App\Service;

use App\Entity\CommunityTest;
use App\Model\View\CommunityTestTeaserView;

class GetCommunityTestTeaserView
{
    public function __construct(private GetCommunityView $getCommunityView)
    {
    }

    public function __invoke(CommunityTest $communityTest): CommunityTestTeaserView
    {
        return new CommunityTestTeaserView(
            $communityTest->getId(),
            ($this->getCommunityView)($communityTest->getCommunity()),
            $communityTest->getCommunityTestCourseSubjectsIds()
        );
    }
}
