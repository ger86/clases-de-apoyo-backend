<?php

namespace App\Controller\Api;

use App\Repository\CommunityTestRepository;
use App\Service\GetCommunityTestView;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ApiCommunityTestController extends AbstractFOSRestController
{

    #[Get(path: '/community-tests/{communityTestId}')]
    public function getCommunityTestAction(
        int $communityTestId,
        CommunityTestRepository $communityTestRepository,
        GetCommunityTestView $getCommunityTestView
    ) {
        $communityTest = $communityTestRepository->find($communityTestId);
        if ($communityTest === null) {
            throw $this->createNotFoundException('No se encontró ese test');
        }
        return $this->view(($getCommunityTestView)($communityTest));
    }
}
