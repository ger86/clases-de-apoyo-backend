<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class Community
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $name = '';

    #[ORM\Column(length: 256, unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: true)]
    private string $slug = '';

    /** @var Collection<int,CommunityTest> */
    #[ORM\OneToMany(targetEntity: CommunityTest::class, mappedBy: 'community')]
    private Collection $communityTests;

    public function __construct()
    {
        $this->communityTests = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name === null ? 'Nueva Comunidad Autónoma' : $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function addCommunityTest(CommunityTest $communityTest): self
    {
        $this->communityTests[] = $communityTest;
        $communityTest->setCommunity($this);

        return $this;
    }

    public function removeCommunityTest(CommunityTest $communityTest): self
    {
        $this->communityTests->removeElement($communityTest);
        return $this;
    }

    /**
     * @return Collection<int,CommunityTest>
     */
    public function getCommunityTests(): Collection
    {
        return $this->communityTests;
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
}
