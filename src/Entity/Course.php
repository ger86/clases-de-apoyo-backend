<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $descriptionFormatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionRaw = null;

    #[ORM\Column(length: 256, unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: true)]
    private string $slug = '';

    /** @var Collection<int,CourseSubject> */
    #[ORM\OneToMany(targetEntity: CourseSubject::class, mappedBy: 'course')]
    private Collection $courseSubjects;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeInterface $updatedAt;


    public function __construct()
    {
        $this->courseSubjects = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->name ?? 'Nuevo curso';
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

    public function addCourseSubject(CourseSubject $courseSubject): self
    {
        $courseSubject->setCourse($this);
        $this->courseSubjects[] = $courseSubject;

        return $this;
    }

    public function removeCourseSubject(CourseSubject $courseSubject): self
    {
        $this->courseSubjects->removeElement($courseSubject);
        return $this;
    }

    /**
     * @return Collection<int,CourseSubject>
     */
    public function getCourseSubjects(): Collection
    {
        return $this->courseSubjects;
    }

    /**
     * @return (int|null)[]
     */
    public function getCourseSubjectsIds(): array
    {
        return $this->courseSubjects->map(fn ($subject) => $subject->getId())->toArray();
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }


    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDescriptionFormatType(): ?string
    {
        return $this->descriptionFormatType;
    }

    public function setDescriptionFormatType(?string $descriptionFormatType): self
    {
        $this->descriptionFormatType = $descriptionFormatType;

        return $this;
    }

    public function getDescriptionRaw(): ?string
    {
        return $this->descriptionRaw;
    }

    public function setDescriptionRaw(?string $descriptionRaw): self
    {
        $this->descriptionRaw = $descriptionRaw;

        return $this;
    }
}
