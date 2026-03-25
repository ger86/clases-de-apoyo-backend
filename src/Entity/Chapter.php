<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(length: 256, unique: true, nullable: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: ChapterBlock::class, inversedBy: 'chapters')]
    #[ORM\JoinColumn(name: 'chapter_block_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?ChapterBlock $chapterBlock;

    /** @var Collection<int,File> */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'chapter', cascade: ['all'])]
    #[ORM\OrderBy(['weight' => 'ASC'])]
    private Collection $files;

    /** @var Collection<int,YoutubeVideo> */
    #[ORM\ManyToMany(targetEntity: YoutubeVideo::class, mappedBy: 'chapters', cascade: ['all'])]
    #[ORM\OrderBy(['title' => 'ASC'])]
    private Collection $youtubeVideos;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->youtubeVideos = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->asString();
    }

    public function asString(): string
    {
        $chapterBlock = $this->chapterBlock;
        if ($this->id === null || $chapterBlock === null) {
            return 'Nuevo capítulo';
        }
        return "{$this->chapterBlock->getCourseSubject()->asString()} {$this->name}";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name ?? '';

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

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setChapterBlock(ChapterBlock $chapterBlock): self
    {
        $this->chapterBlock = $chapterBlock;
        return $this;
    }

    public function getChapterBlock(): ?ChapterBlock
    {
        return $this->chapterBlock;
    }

    public function addFile(File $file): self
    {
        $this->files[] = $file;
        $file->setChapter($this);

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

    public function addYoutubeVideo(YoutubeVideo $youtubeVideo): self
    {
        $this->youtubeVideos[] = $youtubeVideo;

        return $this;
    }

    public function removeYoutubeVideo(YoutubeVideo $youtubeVideo): self
    {
        $this->youtubeVideos->removeElement($youtubeVideo);
        return $this;
    }

    /**
     * @return Collection<int,YoutubeVideo>
     */
    public function getYoutubeVideos(): Collection
    {
        return $this->youtubeVideos;
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
