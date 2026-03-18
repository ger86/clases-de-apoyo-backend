<?php

namespace App\Controller;

use App\Service\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends AbstractController
{
    public function show(Security $security): Response
    {
        $user = $security->getUser();

        return $this->render('views/profile/show.html.twig', [
            'user' => $user
        ]);
    }
}
