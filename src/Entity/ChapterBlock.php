<?php

namespace App\Entity;

use App\Repository\ChapterBlockRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ChapterBlockRepository::class)]
class ChapterBlock
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: true)]
    private string $slug = '';

    #[ORM\ManyToOne(targetEntity: CourseSubject::class, inversedBy: 'chapterBlocks')]
    #[ORM\JoinColumn(name: 'course_subject_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?CourseSubject $courseSubject;

    /** @var Collection<int,Chapter> */
    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'chapterBlock')]
    private Collection $chapters;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;


    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->asString();
    }

    public function asString(): string
    {
        if ($this->name === null || $this->courseSubject === null) {
            return 'Nuevo curso-asignatura';
        }
        return \sprintf('%s %s', $this->courseSubject->asString(), $this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCourseSubject(CourseSubject $courseSubject): self
    {
        $this->courseSubject = $courseSubject;
        return $this;
    }

    public function getCourseSubject(): ?CourseSubject
    {
        return $this->courseSubject;
    }

    public function addChapter(Chapter $chapter): self
    {
        $chapter->setChapterBlock($this);
        $this->chapters[] = $chapter;

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        $this->chapters->removeElement($chapter);
        return $this;
    }

    /**
     * @return Collection<int,Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt instanceof DateTimeImmutable ? $createdAt : DateTimeImmutable::createFromMutable($createdAt);

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt instanceof DateTimeImmutable ? $updatedAt : DateTimeImmutable::createFromMutable($updatedAt);

        return $this;
    }
}
