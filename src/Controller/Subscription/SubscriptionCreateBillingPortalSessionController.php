<?php

namespace App\Controller\Subscription;

use App\Service\Security;
use App\Service\Stripe\StripeCreateBillingPortalSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SubscriptionCreateBillingPortalSessionController extends AbstractController
{

    public function __invoke(
        Security $security,
        StripeCreateBillingPortalSession $stripeCreateBillingPortalSession
    ) {
        $user = $security->getUser();
        if ($user === null || !$user->isPremium()) {
            throw $this->createAccessDeniedException('No puedes acceder aquí');
        }

        $session = ($stripeCreateBillingPortalSession)();

        return new RedirectResponse($session->url);
    }
}
