<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use App\Enum\ExamType;
use App\Repository\ExamRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
class Exam
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $descriptionFormatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionRaw = null;

    #[ORM\Column(type: 'string', length: 256, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'integer', length: 256, nullable: true)]
    private ?int $difficulty;

    #[ORM\Column(length: 256, unique: false)]
    #[Gedmo\Slug(fields: ['name'], updatable: true, unique: true)]
    private $slug;

    /** @var Collection<int,File> */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'exam', cascade: ['all'])]
    #[ORM\OrderBy(['weight' => 'ASC'])]
    private Collection $files;

    #[ORM\ManyToOne(targetEntity: TestYear::class, inversedBy: 'exams')]
    #[ORM\JoinColumn(name: 'test_year', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TestYear $testYear;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->testYear === null ? 'Nuevo examen' :
            "{$this->testYear} {$this->name}";
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function calculateName(): self
    {
        $this->name = "";
        if ($this->testYear !== null) {
            $this->name .= $this->testYear->getYear();
        }
        if ($this->getReadableExamType() !== null) {
            $this->name .= " " . $this->getReadableExamType();
        }
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

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(?int $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getTestYear(): TestYear
    {
        return $this->testYear;
    }

    public function setTestYear(TestYear $testYear): self
    {
        $this->testYear = $testYear;
        $this->calculateName();

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        $this->calculateName();
        return $this;
    }

    public function getReadableExamType(): string
    {
        return ExamType::toString($this->type);
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

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function addFile(File $file): self
    {
        $this->files[] = $file;
        $file->setExam($this);

        return $this;
    }

    public function removeFile(File $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    /**
     * @return Collection<int,File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
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

    public function canSee(?User $user): bool
    {
        $now = new DateTimeImmutable('-2 years');
        $year = $now->format('Y');
        return (int)($this->testYear->getYear()) > $year ||
            $user?->isPremium();
    }
}
