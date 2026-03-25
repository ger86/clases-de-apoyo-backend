<?php

namespace App\Entity;

use App\Repository\TestYearRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: TestYearRepository::class)]
class TestYear
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $year = '';

    #[ORM\ManyToOne(targetEntity: CommunityTestCourseSubject::class, inversedBy: 'testYears')]
    #[ORM\JoinColumn(name: 'community_test_course_subject', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?CommunityTestCourseSubject $communityTestCourseSubject;

    /** @var Collection<int,Exam> */
    #[ORM\OneToMany(targetEntity: Exam::class, mappedBy: 'testYear')]
    #[ORM\OrderBy(['weight' => 'ASC'])]
    private Collection $exams;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->exams = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->communityTestCourseSubject === null ? 'Nuevo año de un test' :
            "{$this->communityTestCourseSubject} {$this->year}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addExam(Exam $exam): self
    {
        $exam->setTestYear($this);
        $this->exams[] = $exam;

        return $this;
    }

    public function removeExam(Exam $exam): self
    {
        $this->exams->removeElement($exam);
        return $this;
    }

    /**
     * @return Collection<int,Exam>
     */
    public function getExams(): Collection
    {
        return $this->exams;
    }

    /**
     * @return int[]
     */
    public function getExamsIds(): array
    {
        return $this->exams->map(fn (Exam $e) => $e->getId())->toArray();
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getCommunityTestCourseSubject(): ?CommunityTestCourseSubject
    {
        return $this->communityTestCourseSubject;
    }

    public function setCommunityTestCourseSubject(CommunityTestCourseSubject $communityTestCourseSubject): self
    {
        $this->communityTestCourseSubject = $communityTestCourseSubject;

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
