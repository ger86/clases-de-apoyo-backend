<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\StripeInvoicePaymentSucceededEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Invoice;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripeInvoicePaymentSucceededSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StripeInvoicePaymentSucceededEvent::class => [
                ['onPaymentSucceeded', 10]
            ]
        ];
    }

    public function onPaymentSucceeded(StripeInvoicePaymentSucceededEvent $event): void
    {
        $invoice = $event->invoice;
        $user = $this->getUser($invoice);
        $payment = $user->processInvoicePayment($invoice);
        $this->em->persist($payment);
        $this->em->flush();
    }



    private function getUser(Invoice $invoice): User
    {
        $user = $this->userRepository->findOneBy(['email' => $invoice->customer_email]);
        if ($user === null) {
            throw new \Exception("User with email {$invoice->customer_email} not found");
        }
        return $user;
    }
}
