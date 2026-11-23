<?php

namespace App\Repository;

use App\Entity\LeadSubscriber;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LeadSubscriber>
 */
class LeadSubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeadSubscriber::class);
    }

    public function findOneByEmail(string $email): ?LeadSubscriber
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findOneByConfirmationToken(string $token): ?LeadSubscriber
    {
        return $this->findOneBy(['confirmationToken' => $token]);
    }

    public function findOneByUnsubscribeToken(string $token): ?LeadSubscriber
    {
        return $this->findOneBy(['unsubscribeToken' => $token]);
    }

    /**
     * Confirmed subscribers whose next follow-up is due, oldest first.
     *
     * @return list<LeadSubscriber>
     */
    public function findDueForFollowUp(DateTimeImmutable $now, int $limit): array
    {
        /** @var list<LeadSubscriber> $subscribers */
        $subscribers = $this->createQueryBuilder('l')
            ->andWhere('l.nextFollowUpAt IS NOT NULL')
            ->andWhere('l.nextFollowUpAt <= :now')
            ->andWhere('l.confirmedAt IS NOT NULL')
            ->andWhere('l.unsubscribedAt IS NULL')
            ->setParameter('now', $now)
            ->orderBy('l.nextFollowUpAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $subscribers;
    }
}
