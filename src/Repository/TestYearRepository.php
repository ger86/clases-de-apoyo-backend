<?php

namespace App\Repository;

use App\Entity\TestYear;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestYear|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestYear|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestYear[]    findAll()
 * @method TestYear[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<TestYear>
 */
class TestYearRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestYear::class);
    }
}
