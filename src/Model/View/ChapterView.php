<?php

namespace App\Model\View;

readonly class ChapterView
{

    /**
     * @param FileView[] $files
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $weight,
        public array $files
    ) {
    }
}
