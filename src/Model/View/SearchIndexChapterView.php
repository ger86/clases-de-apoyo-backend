<?php

namespace App\Model\View;

final readonly class SearchIndexChapterView
{

    public function __construct(
        public int $chapterId,
        public string $chapterName,
        public ?string $description,
        public int $courseId,
        public string $courseName,
        public int $courseSubjectId,
        public string $courseSubjectName,
        public ?string $chapterBlockName
    ) {
    }
}
