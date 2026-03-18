<?php

namespace App\Repository;

use App\Entity\CommunityTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommunityTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommunityTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommunityTest[]    findAll()
 * @method CommunityTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<CommunityTest>
 */
class CommunityTestRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunityTest::class);
    }

    public function findByCommunityAndTestSlugs(string $testSlug, string $communitySlug): ?CommunityTest
    {
        return $this->createQueryBuilder('ct')
            ->leftJoin('ct.community', 'c')
            ->leftJoin('ct.knowledgeTest', 't')
            ->where('c.slug = :communitySlug')->setParameter('communitySlug', $communitySlug)
            ->andWhere('t.slug = :testSlug')->setParameter('testSlug', $testSlug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
