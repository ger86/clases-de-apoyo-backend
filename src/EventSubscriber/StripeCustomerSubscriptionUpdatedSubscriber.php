<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Enum\SubscriptionStatus;
use App\Event\StripeCustomerSubscriptionUpdatedEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Subscription;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeCustomerSubscriptionUpdatedSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StripeCustomerSubscriptionUpdatedEvent::class => [
                ['onCheckoutSessionCompleted', 10]
            ]
        ];
    }

    public function onCheckoutSessionCompleted(StripeCustomerSubscriptionUpdatedEvent $event): void
    {
        $subscription = $event->subscription;
        $user = $this->getUser($subscription);
        $user->processSubscription(
            $subscription->id,
            $subscription->customer,
            $subscription->cancel_at_period_end ? SubscriptionStatus::ENDED : SubscriptionStatus::ACTIVE
        );
        $this->em->flush();
    }


    private function getUser(Subscription $subscription): User
    {
        $user = $this->userRepository->findOneBy(['customerId' => $subscription->customer]);
        if ($user === null) {
            throw new \Exception("User with customer {$subscription->customer} not found");
        }
        return $user;
    }
}
