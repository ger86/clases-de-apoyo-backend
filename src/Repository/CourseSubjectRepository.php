<?php

namespace App\Repository;

use App\Entity\CourseSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CourseSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourseSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourseSubject[]    findAll()
 * @method CourseSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<CourseSubject>
 */
class CourseSubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourseSubject::class);
    }

    public function findByCourseAndSubjectSlugs($params): ?CourseSubject
    {
        return $this->createQueryBuilder('cs')
            ->leftJoin('cs.course', 'c')
            ->leftJoin('cs.subject', 's')
            ->where('c.slug = :courseSlug')->setParameter('courseSlug', $params['courseSlug'])
            ->andWhere('s.slug = :subjectSlug')->setParameter('subjectSlug', $params['subjectSlug'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
