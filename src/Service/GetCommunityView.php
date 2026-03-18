<?php

namespace App\Service;

use App\Entity\Community;
use App\Model\View\CommunityView;

class GetCommunityView
{
    public function __invoke(Community $community): CommunityView
    {
        return new CommunityView(
            $community->getId(),
            $community->getName(),
            $community->getSlug()
        );
    }
}
