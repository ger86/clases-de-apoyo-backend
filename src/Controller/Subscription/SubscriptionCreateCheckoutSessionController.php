<?php

namespace App\Controller\Subscription;

use App\Service\Security;
use App\Service\Stripe\StripeCreateCheckoutSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SubscriptionCreateCheckoutSessionController extends AbstractController
{

    public function __invoke(
        Security $security,
        StripeCreateCheckoutSession $stripeCreateCheckoutSession
    ) {
        $user = $security->getUser();
        if ($user === null || $user->isPremium()) {
            throw $this->createAccessDeniedException('No puedes acceder aquí');
        }

        $session = ($stripeCreateCheckoutSession)();

        return new RedirectResponse($session->url);
    }
}
