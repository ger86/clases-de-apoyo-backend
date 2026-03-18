<?php

namespace App\Controller\Subscription;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubscriptionPaymentSuccessController extends AbstractController
{
    public function __invoke()
    {
        return $this->render('views/subscription/payment_success/payment_success.html.twig');
    }
}
