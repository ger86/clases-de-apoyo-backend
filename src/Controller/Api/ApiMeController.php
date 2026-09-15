<?php

namespace App\Controller\Api;

use App\Service\Auth\DeleteAccount;
use App\Service\GetMeView;
use App\Service\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ApiMeController extends AbstractFOSRestController
{
    #[Get(path: '/me')]
    #[IsGranted('ROLE_USER')]
    public function getMeAction(Security $security, GetMeView $getMeView): View
    {
        return $this->view(($getMeView)($security->getSafeUser()));
    }

    #[Delete(path: '/me')]
    #[IsGranted('ROLE_USER')]
    public function deleteMeAction(Security $security, DeleteAccount $deleteAccount): View
    {
        ($deleteAccount)($security->getSafeUser());

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
