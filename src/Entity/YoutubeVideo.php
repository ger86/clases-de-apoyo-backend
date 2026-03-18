<?php

namespace App\Entity;

use App\Repository\YoutubeVideoRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: YoutubeVideoRepository::class)]
class YoutubeVideo
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $title;

    #[ORM\Column(length: 256, unique: true)]
    #[Gedmo\Slug(fields: ['title'], updatable: true)]
    private string $slug;

    #[ORM\Column(type: 'string', length: 1024)]
    private string $youtubeId;

    /** @var Collection<int,Chapter> */
    #[ORM\ManyToMany(targetEntity: Chapter::class, inversedBy: 'youtubeVideos')]
    #[ORM\JoinTable(name: 'youtube_video_chapter')]
    private Collection $chapters;

    #[ORM\ManyToOne(targetEntity: SonataMediaMedia::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id')]
    private ?SonataMediaMedia $image = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $descriptionFormatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionRaw = null;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->title === null ? 'Nuevo vídeo' : $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getYoutubeId(): string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(string $youtubeId): self
    {
        $this->youtubeId = $youtubeId;

        return $this;
    }

    public function addChapter(Chapter $chapter): self
    {
        $this->chapters[] = $chapter;
        $chapter->addYoutubeVideo($this);

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

    public function getImage(): ?SonataMediaMedia
    {
        return $this->image;
    }

    public function setImage(?SonataMediaMedia $image): self
    {
        $this->image = $image;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt)
    {
        $this->excerpt = $excerpt;

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
}
