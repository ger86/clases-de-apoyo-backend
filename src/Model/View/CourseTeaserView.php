<?php

namespace App\Model\View;

readonly class CourseTeaserView
{

    /**
     * @param int[] $courseSubjects
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $weight,
        public array $courseSubjects
    ) {
    }
}
