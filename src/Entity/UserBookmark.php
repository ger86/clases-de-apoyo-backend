<?php

namespace App\Entity;

use App\Repository\UserBookmarkRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * One row per file a student saved to read later. Rows follow the account, so the app and
 * the website show the same list. Both foreign keys cascade: deleting the file or the
 * account deletes the bookmark too.
 */
#[ORM\Entity(repositoryClass: UserBookmarkRepository::class)]
#[ORM\Table(name: 'user_bookmark')]
#[ORM\UniqueConstraint(name: 'user_bookmark_unique_file', columns: ['user_id', 'file_id'])]
class UserBookmark
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: File::class)]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
    private File $file;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $bookmarkedAt;

    public function __construct(User $user, File $file, DateTimeImmutable $bookmarkedAt)
    {
        $this->user = $user;
        $this->file = $file;
        $this->bookmarkedAt = $bookmarkedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function getBookmarkedAt(): DateTimeImmutable
    {
        return $this->bookmarkedAt;
    }
}
