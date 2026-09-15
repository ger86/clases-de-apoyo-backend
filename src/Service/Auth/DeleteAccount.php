<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use App\Service\Stripe\StripeCancelSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Deletes an account for good, as required by App Store guideline 5.1.1(v).
 *
 * An Apple subscription cannot be cancelled from a server, so the app tells the
 * user to cancel it in the Apple settings. A Stripe subscription is cancelled here,
 * otherwise the user would keep paying for an account that no longer exists.
 */
final class DeleteAccount
{
    public function __construct(
        private EntityManagerInterface $em,
        private ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private StripeCancelSubscription $stripeCancelSubscription,
        private ApiTokenManager $apiTokenManager,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(User $user): void
    {
        $subscriptionId = $user->getSubscriptionId();
        if ($subscriptionId !== null && $subscriptionId !== '') {
            try {
                ($this->stripeCancelSubscription)($subscriptionId);
            } catch (Throwable $exception) {
                $this->logger->error('Stripe subscription could not be cancelled on account deletion', [
                    'userId' => $user->getId(),
                    'subscriptionId' => $subscriptionId,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $this->apiTokenManager->revokeAll($user);
        $this->resetPasswordRequestRepository->deleteByUser($user);

        $this->em->remove($user);
        $this->em->flush();
    }
}
