<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBookmark;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBookmark|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBookmark|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBookmark[]    findAll()
 * @method UserBookmark[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<UserBookmark>
 */
class UserBookmarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBookmark::class);
    }

    /**
     * The newest first, with the file already loaded so the list needs a single query.
     *
     * @return UserBookmark[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->addSelect('f')
            ->innerJoin('b.file', 'f')
            ->where('b.user = :user')
            ->setParameter('user', $user)
            ->orderBy('b.bookmarkedAt', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndFileId(User $user, int $fileId): ?UserBookmark
    {
        return $this->findOneBy([
            'user' => $user,
            'file' => $fileId,
        ]);
    }
}
