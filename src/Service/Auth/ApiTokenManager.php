<?php

namespace App\Service\Auth;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\ApiTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class ApiTokenManager
{
    private const LIFETIME = '+60 days';

    /**
     * Sliding expiry is only written once a day so that ordinary API reads stay read-only.
     */
    private const TOUCH_AFTER = '-1 day';

    public function __construct(
        private EntityManagerInterface $em,
        private ApiTokenRepository $apiTokenRepository
    ) {
    }

    public function issue(User $user, ?string $deviceName = null): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $apiToken = new ApiToken(
            $user,
            self::hash($plainToken),
            $this->expiryFrom(new DateTimeImmutable()),
            $deviceName
        );

        $this->em->persist($apiToken);
        $this->em->flush();

        return $plainToken;
    }

    public function findValid(string $plainToken): ?ApiToken
    {
        $apiToken = $this->apiTokenRepository->findOneByHash(self::hash($plainToken));
        if ($apiToken === null) {
            return null;
        }

        $now = new DateTimeImmutable();
        if ($apiToken->isExpired($now)) {
            $this->revoke($apiToken);

            return null;
        }

        if ($apiToken->getLastUsedAt() < $now->modify(self::TOUCH_AFTER)) {
            $apiToken->touch($now, $this->expiryFrom($now));
            $this->em->flush();
        }

        return $apiToken;
    }

    public function revokeByPlainToken(string $plainToken): void
    {
        $apiToken = $this->apiTokenRepository->findOneByHash(self::hash($plainToken));
        if ($apiToken === null) {
            return;
        }

        $this->revoke($apiToken);
    }

    public function revoke(ApiToken $apiToken): void
    {
        $this->em->remove($apiToken);
        $this->em->flush();
    }

    public function revokeAll(User $user): void
    {
        foreach ($this->apiTokenRepository->findByUser($user) as $apiToken) {
            $this->em->remove($apiToken);
        }

        $this->em->flush();
    }

    private function expiryFrom(DateTimeImmutable $now): DateTimeImmutable
    {
        return $now->modify(self::LIFETIME);
    }

    private static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
