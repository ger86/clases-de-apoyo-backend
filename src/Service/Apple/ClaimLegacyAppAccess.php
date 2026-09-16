<?php

namespace App\Service\Apple;

use App\Entity\User;
use App\Enum\PremiumProvider;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives a free year to the people who bought the app when it was a paid download.
 *
 * The app was sold for 8,99 € until version 9.0.0 made it free. Those buyers paid for the
 * whole material once, so gating it behind a subscription would take away what they bought.
 * StoreKit tells the app when the account first got the app, and anything bought before the
 * app became free earns the year.
 */
final class ClaimLegacyAppAccess
{
    private const FREE_PERIOD = '+1 year';

    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire('%app.apple.legacy_access_cutoff%')]
        private string $cutoff
    ) {
    }

    public function __invoke(User $user, DateTimeImmutable $originalPurchaseDate, ?string $appTransactionId): void
    {
        if ($user->hasClaimedLegacyAppAccess()) {
            throw new AppleTransactionException(
                'Esta cuenta ya ha recibido el año gratuito.',
                'legacy_access_already_claimed',
                Response::HTTP_CONFLICT
            );
        }

        if ($originalPurchaseDate >= new DateTimeImmutable($this->cutoff)) {
            throw new AppleTransactionException(
                'Esta descarga no corresponde a una compra de la app de pago.',
                'legacy_access_not_eligible',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $now = new DateTimeImmutable();
        $user->claimLegacyAppAccess($now, $appTransactionId);
        // A different provider on purpose: a later Apple or Stripe payment must never cut
        // the free year short, and the year must never cut a longer paid period short.
        $user->grantPremiumUntil($now->modify(self::FREE_PERIOD), PremiumProvider::LEGACY_APP);

        $this->em->flush();
    }
}
