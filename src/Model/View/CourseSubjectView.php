<?php

namespace App\Model\View;

readonly class CourseSubjectView
{

    /**
     * @param ChapterBlockView[] $chapterBlocks
     */
    public function __construct(
        public int $id,
        public SubjectView $subject,
        public ?string $description,
        public ?int $weight,
        public array $chapterBlocks
    ) {
    }
}
