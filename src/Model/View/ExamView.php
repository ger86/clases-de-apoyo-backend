<?php

namespace App\Model\View;

readonly class ExamView
{

    /**
     * @param FileView[] $files
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $weight,
        public ?int $difficulty,
        public array $files
    ) {
    }
}
