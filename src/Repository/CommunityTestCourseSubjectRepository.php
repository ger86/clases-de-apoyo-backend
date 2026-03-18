<?php

namespace App\Repository;

use App\Entity\CommunityTestCourseSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CommunityTestCourseSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommunityTestCourseSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommunityTestCourseSubject[]    findAll()
 * @method CommunityTestCourseSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<CommunityTestCourseSubject>
 */
class CommunityTestCourseSubjectRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommunityTestCourseSubject::class);
    }

    public function findByCommunityAndTestAndCourseSubjectSlugs(string $testSlug, string $communitySlug, string $courseSubjectSlug): ?CommunityTestCourseSubject
    {
        return $this->createQueryBuilder('cs')
            ->leftJoin('cs.courseSubject', 'coursesubject')
            ->leftJoin('coursesubject.subject', 's')
            ->leftJoin('cs.communityTest', 'ct')
            ->leftJoin('ct.community', 'c')
            ->leftJoin('ct.knowledgeTest', 't')
            ->where('c.slug = :communitySlug')
            ->setParameter('communitySlug', $communitySlug)
            ->andWhere('t.slug = :testSlug')
            ->setParameter('testSlug', $testSlug)
            ->andWhere('s.slug = :courseSubjectSlug')
            ->setParameter('courseSubjectSlug', $courseSubjectSlug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
