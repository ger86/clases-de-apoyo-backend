<?php

namespace App\Service\Stripe;

use App\Enum\StripeConsts;
use Stripe\{Event, Webhook};
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GetEventFromWebhook
{

    public function __construct(
        private RequestStack $requestStack,
        #[Autowire('%app.stripe.webhook_secret%')]
        private string $stripeWebhookSecret
    ) {
    }

    public function __invoke(): Event
    {
        $request = $this->requestStack->getCurrentRequest();
        $stripeSignatureHeader = $request->server->get(StripeConsts::HTTP_STRIPE_SIGNATURE);
        $payload = $request->getContent();
        $event = Webhook::constructEvent($payload, $stripeSignatureHeader, $this->stripeWebhookSecret);
        return $event;
    }
}
