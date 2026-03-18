<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $title = '';

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['title'])]
    private string $slug = '';

    #[ORM\Column(type: 'float')]
    private float $price = 0.0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $formatType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $textRaw = null;

    #[ORM\ManyToOne(targetEntity: SonataMediaMedia::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id')]
    private ?SonataMediaMedia $image = null;

    #[ORM\ManyToOne(targetEntity: SonataMediaMedia::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'file_id', referencedColumnName: 'id')]
    private ?SonataMediaMedia $file = null;

    public function __toString()
    {
        return $this->title === null ? 'Nuevo libro' : "{$this->title}";
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getFormatType(): ?string
    {
        return $this->formatType;
    }

    public function setFormatType(?string $formatType): self
    {
        $this->formatType = $formatType;

        return $this;
    }

    public function getTextRaw(): ?string
    {
        return $this->textRaw;
    }

    public function setTextRaw(?string $textRaw): self
    {
        $this->textRaw = $textRaw;

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

    public function getImage(): ?SonataMediaMedia
    {
        return $this->image;
    }

    public function setImage(?SonataMediaMedia $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?SonataMediaMedia
    {
        return $this->file;
    }

    public function setFile(?SonataMediaMedia $file): self
    {
        $this->file = $file;

        return $this;
    }
}
