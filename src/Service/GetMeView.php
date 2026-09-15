<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\PremiumProvider;
use App\Model\View\MeView;
use App\Service\Apple\EnsureAppAccountToken;
use DateTimeInterface;

class GetMeView
{
    public function __construct(private EnsureAppAccountToken $ensureAppAccountToken)
    {
    }

    public function __invoke(User $user): MeView
    {
        $premiumUntil = $user->getPremiumUntil();

        return new MeView(
            (int) $user->getId(),
            $user->getEmail(),
            $user->isVerified(),
            $user->isPremium(),
            $premiumUntil?->format(DateTimeInterface::ATOM),
            $user->getPremiumProvider(),
            $user->getPremiumProvider() === PremiumProvider::APPLE,
            ($this->ensureAppAccountToken)($user)
        );
    }
}
