<?php

namespace App\Model\View;

readonly class CourseView
{

    /**
     * @param CourseSubjectView[] $courseSubjects
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
