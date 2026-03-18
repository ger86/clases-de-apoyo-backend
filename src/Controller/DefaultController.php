<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{

    public function home(): Response
    {
        return $this->render('views/home/home.html.twig');
    }

    public function legalNotice(): Response
    {
        return $this->render('views/pages/legal_notice.html.twig');
    }

    public function contact(): Response
    {
        return $this->render('views/pages/contact.html.twig');
    }
}
