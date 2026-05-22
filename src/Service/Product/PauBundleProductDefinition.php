<?php

namespace App\Service\Product;

final class PauBundleProductDefinition
{
    /**
     * @param array<int, array{key: string, label: string, path: string, filename: string, description?: string}> $files
     */
    public function __construct(
        private string $code,
        private string $slug,
        private string $title,
        private string $subjectSlug,
        private string $subjectName,
        private string $communitySlug,
        private string $communityName,
        private string $knowledgeTestSlug,
        private string $courseSlug,
        private string $yearRange,
        private int $priceCents,
        private string $currency,
        private string $description,
        private int $statementCount,
        private int $solutionCount,
        private int $completePages,
        private int $statementPages,
        private int $solutionPages,
        private array $files
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubjectSlug(): string
    {
        return $this->subjectSlug;
    }

    public function getSubjectName(): string
    {
        return $this->subjectName;
    }

    public function getCommunitySlug(): string
    {
        return $this->communitySlug;
    }

    public function getCommunityName(): string
    {
        return $this->communityName;
    }

    public function getKnowledgeTestSlug(): string
    {
        return $this->knowledgeTestSlug;
    }

    public function getCourseSlug(): string
    {
        return $this->courseSlug;
    }

    public function getYearRange(): string
    {
        return $this->yearRange;
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->priceCents / 100, 2, ',', '.') . ' €';
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatementCount(): int
    {
        return $this->statementCount;
    }

    public function getSolutionCount(): int
    {
        return $this->solutionCount;
    }

    public function getCompletePages(): int
    {
        return $this->completePages;
    }

    public function getStatementPages(): int
    {
        return $this->statementPages;
    }

    public function getSolutionPages(): int
    {
        return $this->solutionPages;
    }

    /**
     * @return array<int, array{key: string, label: string, path: string, filename: string, description?: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
