<?php

namespace App\Repository;

use App\Entity\Exam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exam|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exam|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exam[]    findAll()
 * @method Exam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<Exam>
 */
class ExamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exam::class);
    }

    public function findByCriteria(
        string $testSlug,
        string $communitySlug,
        string $courseSubjectSlug,
        string $examSlug
    ): ?Exam {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.testYear', 'testyear')
            ->leftJoin('testyear.communityTestCourseSubject', 'ctcs')
            ->leftJoin('ctcs.courseSubject', 'coursesubject')
            ->leftJoin('coursesubject.subject', 's')
            ->leftJoin('ctcs.communityTest', 'ct')
            ->leftJoin('ct.community', 'c')
            ->leftJoin('ct.knowledgeTest', 't')
            ->andWhere('t.slug = :testSlug')->setParameter('testSlug', $testSlug)
            ->andWhere('c.slug = :communitySlug')->setParameter('communitySlug', $communitySlug)
            ->andWhere('s.slug = :courseSubjectSlug')->setParameter('courseSubjectSlug', $courseSubjectSlug)
            ->andWhere('e.slug = :examSlug')->setParameter('examSlug', $examSlug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
