<?php

namespace App\Model\View;

readonly class ChapterBlockView
{

    /**
     * @param ChapterView[] $chapters
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $weight,
        public array $chapters
    ) {
    }
}
