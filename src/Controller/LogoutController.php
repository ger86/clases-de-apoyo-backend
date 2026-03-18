<?php

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutController extends AbstractController
{
    public function __invoke()
    {
        throw new LogicException('This controller is never called');
    }
}
