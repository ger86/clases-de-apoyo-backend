<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Enum\SubscriptionStatus;
use App\Event\StripeCheckoutSessionCompletedEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeCheckoutSessionCompletedSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StripeCheckoutSessionCompletedEvent::class => [
                ['onCheckoutSessionCompleted', 10]
            ]
        ];
    }

    public function onCheckoutSessionCompleted(StripeCheckoutSessionCompletedEvent $event): void
    {
        $session = $event->session;
        $user = $this->getUser($session);
        $user->processSubscription($session->subscription, $session->customer, SubscriptionStatus::ACTIVE);
        $this->em->flush();
    }



    private function getUser(Session $session): User
    {
        $user = $this->userRepository->find($session->client_reference_id);
        if ($user === null) {
            throw new \Exception("User with email {$session->customer_email} not found");
        }
        return $user;
    }
}
