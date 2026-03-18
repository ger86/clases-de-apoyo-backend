<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class File
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256, nullable: true)]
    private ?string $name = '';

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight = null;

    #[ORM\ManyToOne(targetEntity: SonataMediaMedia::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id')]
    private ?SonataMediaMedia $file;

    #[ORM\ManyToOne(targetEntity: Chapter::class, inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'chapter_id', referencedColumnName: 'id')]
    private ?Chapter $chapter;

    #[ORM\ManyToOne(targetEntity: Exam::class, inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'exam_id', referencedColumnName: 'id')]
    private ?Exam $exam;

    public function __toString()
    {
        return $this->name ?? 'Nuevo archivo';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int  $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getFile(): ?SonataMediaMedia
    {
        return $this->file;
    }

    public function setFile(SonataMediaMedia $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(Chapter $chapter): self
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getExam(): ?Exam
    {
        return $this->exam;
    }

    public function setExam(Exam $exam): self
    {
        $this->exam = $exam;

        return $this;
    }

    public function canSee(?User $user): bool
    {
        $filenameLower = strtolower($this->name);
        $allowed = ['apuntes', 'enunciados', 'problemas'];
        if (strpos($filenameLower, 'comentario') !== false) {
            return true;
        }
        if (strpos($filenameLower, 'resumen') !== false) {
            return true;
        }
        return \in_array($filenameLower, $allowed, true) || $user?->isPremium();
    }
}
