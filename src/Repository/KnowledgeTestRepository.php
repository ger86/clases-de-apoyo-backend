<?php

namespace App\Repository;

use App\Entity\KnowledgeTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method KnowledgeTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method KnowledgeTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method KnowledgeTest[]    findAll()
 * @method KnowledgeTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<KnowledgeTest>
 */
class KnowledgeTestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KnowledgeTest::class);
    }
}
