<?php

namespace App\Controller\Subscription;

use App\Service\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubscriptionPaymentController extends AbstractController
{

    public function __invoke(Security $security)
    {
        $user = $security->getUser();
        if ($user === null) {
            throw $this->createAccessDeniedException('No puedes acceder aquí');
        }
        if ($user->isPremium()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('views/subscription/payment/payment.html.twig');
    }
}
