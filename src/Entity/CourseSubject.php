<?php

namespace App\Entity;

use App\Repository\CourseSubjectRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CourseSubjectRepository::class)]
class CourseSubject
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Subject $subject;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'courseSubjects')]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Course $course;

    /** @var Collection<int,ChapterBlock> */
    #[ORM\OneToMany(targetEntity: ChapterBlock::class, mappedBy: 'courseSubject')]
    private Collection $chapterBlocks;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $descriptionFormatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionRaw = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->chapterBlocks = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->asString();
    }

    public function asString(): string
    {
        return $this->course === null ? 'Nuevo curso-asignatura' : "{$this->course} {$this->subject}";
    }

    public function setSubject(Subject $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function getSubjectSlug(): string
    {
        return $this->subject->getSlug();
    }

    public function getSubjectName(): string
    {
        return $this->subject->getName();
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;
        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function addChapterBlock(ChapterBlock $chapterBlock): self
    {
        $chapterBlock->setCourseSubject($this);
        $this->chapterBlocks[] = $chapterBlock;

        return $this;
    }

    public function removeChapterBlock(ChapterBlock $chapterBlock): self
    {
        $this->chapterBlocks->removeElement($chapterBlock);
        return $this;
    }

    /**
     * @return Collection<int,ChapterBlock>
     */
    public function getChapterBlocks(): Collection
    {
        return $this->chapterBlocks;
    }

    /**
     * @return int[]
     */
    public function getChapterBlocksIds(): array
    {
        return $this->chapterBlocks->map(fn ($chapterBlock) => $chapterBlock->getId())->toArray();
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

    public function getId(): ?int
    {
        return $this->id;
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
