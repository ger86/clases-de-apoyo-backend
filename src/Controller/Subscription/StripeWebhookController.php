<?php

namespace App\Controller\Subscription;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Stripe\StripeProcessWebhook;
use Exception;
use Psr\Log\LoggerInterface;

class StripeWebhookController extends AbstractController
{

    public function __invoke(StripeProcessWebhook $stripeProcessWebhook, LoggerInterface $logger): Response
    {
        $response = new Response();
        try {
            ($stripeProcessWebhook)();
            $response->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            $logger->critical($e->getMessage());
            $response->setContent('error');
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
