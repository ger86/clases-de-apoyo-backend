<?php

namespace App\Repository;

use App\Entity\Chapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chapter[]    findAll()
 * @method Chapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<Chapter>
 */
class ChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapter::class);
    }

    public function findByCourseAndSubjectAndChapterSlugs($params): ?Chapter
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.chapterBlock', 'cb')
            ->leftJoin('cb.courseSubject', 'cs')
            ->leftJoin('cs.course', 'course')
            ->leftJoin('cs.subject', 's')
            ->where('course.slug = :courseSlug')->setParameter('courseSlug', $params['courseSlug'])
            ->andWhere('s.slug = :subjectSlug')->setParameter('subjectSlug', $params['subjectSlug'])
            ->andWhere('c.slug = :chapterSlug')->setParameter('chapterSlug', $params['chapterSlug'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
