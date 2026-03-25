<?php

namespace App\Entity;

use App\Repository\CommunityTestRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: CommunityTestRepository::class)]
class CommunityTest
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Community::class, inversedBy: 'communityTests')]
    #[ORM\JoinColumn(name: 'community', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Community $community;

    #[ORM\ManyToOne(targetEntity: KnowledgeTest::class, inversedBy: 'communityTests')]
    #[ORM\JoinColumn(name: 'knowledge_test', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?KnowledgeTest $knowledgeTest;

    /** @var Collection<int,CommunityTestCourseSubject> */
    #[ORM\OneToMany(targetEntity: CommunityTestCourseSubject::class, mappedBy: 'communityTest')]
    private $communityTestCourseSubjects;

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
        $this->communityTestCourseSubjects = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->community === null ? 'Nuevo test para una Comunidad Autónoma' :
            "{$this->knowledgeTest} {$this->community}";
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getKnowledgeTest(): ?KnowledgeTest
    {
        return $this->knowledgeTest;
    }

    public function setKnowledgeTest(KnowledgeTest $test): self
    {
        $this->knowledgeTest = $test;

        return $this;
    }

    public function addCommunityTest(CommunityTestCourseSubject $communityTestCourseSubject): self
    {
        $this->communityTestCourseSubjects[] = $communityTestCourseSubject;
        $communityTestCourseSubject->setCommunityTest($this);

        return $this;
    }

    public function removeCommunityTest(CommunityTestCourseSubject $communityTestCourseSubject): self
    {
        $this->communityTestCourseSubjects->removeElement($communityTestCourseSubject);
        return $this;
    }

    /**
     * @return Collection<int,CommunityTestCourseSubject>
     */
    public function getCommunityTestCourseSubjects(): Collection
    {
        return $this->communityTestCourseSubjects;
    }

    /**
     * @return int[]
     */
    public function getCommunityTestCourseSubjectsIds(): array
    {
        return $this->communityTestCourseSubjects->map(fn ($c) => $c->getId())->toArray();
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

    public function getId(): ?int
    {
        return $this->id;
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
