<?php

namespace App\Service\Stripe;

use App\Service\Security;
use Stripe\Stripe;
use Stripe\BillingPortal\Session;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeCreateBillingPortalSession
{

    public function __construct(
        #[Autowire('%app.stripe.secret_key%')]
        private string $secretKey,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function __invoke(): Session
    {
        $currentUser = $this->security->getSafeUser();
        Stripe::setApiKey($this->secretKey);

        $returnUrl = $this->urlGenerator->generate('app_profile_show', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        $stripeCustomerId = $currentUser->getCustomerId();

        return Session::create([
            'customer' => $stripeCustomerId,
            'return_url' => $returnUrl,
        ]);
    }
}
