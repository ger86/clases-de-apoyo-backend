<?php

namespace App\Repository;

use App\Entity\ChapterBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChapterBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChapterBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChapterBlock[]    findAll()
 * @method ChapterBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<ChapterBlock>
 */
class ChapterBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChapterBlock::class);
    }
}
