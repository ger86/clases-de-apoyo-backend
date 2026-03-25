<?php

namespace App\Entity;

use App\Repository\CommunityTestCourseSubjectRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CommunityTestCourseSubjectRepository::class)]
class CommunityTestCourseSubject
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\ManyToOne(targetEntity: CommunityTest::class, inversedBy: 'communityTestCourseSubjects')]
    #[ORM\JoinColumn(name: 'community_test', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?CommunityTest $communityTest;

    #[ORM\ManyToOne(targetEntity: CourseSubject::class)]
    #[ORM\JoinColumn(name: 'course_subject', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?CourseSubject $courseSubject;

    /** @var Collection<int,TestYear> */
    #[ORM\OneToMany(targetEntity: TestYear::class, mappedBy: 'communityTestCourseSubject')]
    #[ORM\OrderBy(['year' => 'DESC'])]
    private Collection $testYears;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $descriptionFormatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionRaw = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->testYears = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->communityTest === null ? 'Nuevo asignatura para un test de una comunidad autónoma' :
            "{$this->communityTest} {$this->courseSubject}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommunityTest(): ?CommunityTest
    {
        return $this->communityTest;
    }

    public function setCommunityTest(CommunityTest $communityTest): self
    {
        $this->communityTest = $communityTest;

        return $this;
    }

    public function getCourseSubject(): ?CourseSubject
    {
        return $this->courseSubject;
    }

    public function setCourseSubject(CourseSubject $courseSubject): self
    {
        $this->courseSubject = $courseSubject;

        return $this;
    }

    public function addTestYear(TestYear $testYear): self
    {
        $this->testYears[] = $testYear;
        $testYear->setCommunityTestCourseSubject($this);

        return $this;
    }

    public function removeTestYear(TestYear $testYear): self
    {
        $this->testYears->removeElement($testYear);
        return $this;
    }

    /**
     * @return Collection<int,TestYear>
     */
    public function getTestYears(): Collection
    {
        return $this->testYears;
    }

    /**
     * @return int[]
     */
    public function getTestYearsIds(): array
    {
        return $this->testYears->map(fn ($t) => $t->getId())->toArray();
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
