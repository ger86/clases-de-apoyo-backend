<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserProgress;
use App\Model\ProgressTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProgress[]    findAll()
 * @method UserProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<UserProgress>
 */
class UserProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProgress::class);
    }

    /**
     * @return UserProgress[]
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['doneAt' => 'ASC']);
    }

    public function findOneByUserAndTarget(User $user, ProgressTarget $target): ?UserProgress
    {
        return $this->findOneBy([
            'user' => $user,
            'kind' => $target->kind,
            'targetId' => $target->id,
        ]);
    }
}
