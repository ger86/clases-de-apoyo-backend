<?php

namespace App\Controller\Api;

use App\Repository\KnowledgeTestRepository;
use App\Service\GetKnowledgeTestView;
use FOS\RestBundle\Controller\Annotations\{Get};
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ApiKnowledgeTestController extends AbstractFOSRestController
{

    #[Get(path: '/knowledge-tests/{knowledgeTestSlug}')]
    public function getKnowledgeTestAction(
        KnowledgeTestRepository $knowledgeTestRepository,
        string $knowledgeTestSlug,
        GetKnowledgeTestView $getKnowledgeTestView
    ) {
        $knowledgeTest = $knowledgeTestRepository->findOneBy(['slug' => $knowledgeTestSlug]);
        if ($knowledgeTest === null) {
            throw $this->createNotFoundException('No se encontró ese test');
        }
        return $this->view(($getKnowledgeTestView)($knowledgeTest));
    }
}
