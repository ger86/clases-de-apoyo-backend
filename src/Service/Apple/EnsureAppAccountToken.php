<?php

namespace App\Service\Apple;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Every account gets one UUID that the app passes to StoreKit as appAccountToken.
 * Apple echoes it back in transactions and in server notifications, which is how a
 * purchase is matched to an account even when the notification arrives much later.
 */
final class EnsureAppAccountToken
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(User $user): string
    {
        $appAccountToken = $user->getAppleAppAccountToken();
        if ($appAccountToken !== null && $appAccountToken !== '') {
            return $appAccountToken;
        }

        $appAccountToken = Uuid::v4()->toRfc4122();
        $user->setAppleAppAccountToken($appAccountToken);
        $this->em->flush();

        return $appAccountToken;
    }
}
