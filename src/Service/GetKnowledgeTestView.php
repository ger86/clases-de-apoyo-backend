<?php

namespace App\Service;

use App\Entity\CommunityTest;
use App\Entity\KnowledgeTest;
use App\Model\View\KnowledgeTestView;

class GetKnowledgeTestView
{
    public function __construct(private GetCommunityTestView $getCommunityTestView)
    {
    }

    public function __invoke(KnowledgeTest $knowledgeTest): KnowledgeTestView
    {
        return new KnowledgeTestView(
            $knowledgeTest->getId(),
            $knowledgeTest->getName(),
            $knowledgeTest->getDescription(),
            array_map(
                fn (CommunityTest $communityTest) => ($this->getCommunityTestView)($communityTest),
                $knowledgeTest->getCommunityTests()->toArray()
            )
        );
    }
}
