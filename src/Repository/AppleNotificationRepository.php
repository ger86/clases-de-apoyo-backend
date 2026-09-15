<?php

namespace App\Repository;

use App\Entity\AppleNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AppleNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppleNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppleNotification[]    findAll()
 * @method AppleNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<AppleNotification>
 */
class AppleNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppleNotification::class);
    }

    public function findOneByUuid(string $notificationUuid): ?AppleNotification
    {
        return $this->findOneBy(['notificationUuid' => $notificationUuid]);
    }
}
