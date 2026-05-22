<?php

namespace App\Controller\Subscription;

use App\Service\Security;
use App\Service\Stripe\StripeCreateCheckoutSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionCreateCheckoutSessionController extends AbstractController
{

    public function __invoke(
        Request $request,
        Security $security,
        StripeCreateCheckoutSession $stripeCreateCheckoutSession
    ) {
        $user = $security->getUser();
        if ($user === null || $user->isPremium()) {
            throw $this->createAccessDeniedException('No puedes acceder aquí');
        }

        if (!$this->isCsrfTokenValid('subscription_checkout', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('La sesión de pago no es válida.');
        }

        $plan = (string) $request->request->get('plan');
        if (!\in_array($plan, [StripeCreateCheckoutSession::PLAN_MONTHLY, StripeCreateCheckoutSession::PLAN_YEARLY], true)) {
            throw $this->createAccessDeniedException('La opción de pago no es válida.');
        }

        $session = ($stripeCreateCheckoutSession)($plan);

        return new RedirectResponse($session->url);
    }
}
