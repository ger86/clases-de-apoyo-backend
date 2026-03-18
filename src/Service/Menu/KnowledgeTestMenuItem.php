<?php

namespace App\Service\Menu;

use App\Model\MenuLink;
use App\Repository\KnowledgeTestRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgeTestMenuItem implements MenuItemGeneratorInterface
{

    public function __construct(
        private KnowledgeTestRepository $knowledgeTestRepository,
        private UrlGeneratorInterface $router
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return 0;
    }

    public function getMenuLinks(): array
    {
        $knowledgeTests = $this->knowledgeTestRepository->findAll();
        $menuLinks = [];
        foreach ($knowledgeTests as $knowledgeTest) {
            $communityTestsLinks = [];

            foreach ($knowledgeTest->getCommunityTests() as $communityTest) {
                $url = $this->router->generate(
                    'community_test',
                    [
                        'testSlug' => $knowledgeTest->getSlug(),
                        'communitySlug' => $communityTest->getCommunity()->getSlug()
                    ]
                );
                $communityTestsLinks[] = new MenuLink($communityTest->getCommunity()->getName(), $url);
            }
            $url = $this->router->generate(
                'knowledge_test',
                [
                    'testSlug' => $knowledgeTest->getSlug(),
                ]
            );

            $menuLinks[] = new MenuLink($knowledgeTest->getName(), $url, $communityTestsLinks);
        }

        return $menuLinks;
    }
}
