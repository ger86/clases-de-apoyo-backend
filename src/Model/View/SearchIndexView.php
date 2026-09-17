<?php

namespace App\Model\View;

final readonly class SearchIndexView
{

    /**
     * @param SearchIndexExamView[] $exams
     * @param SearchIndexChapterView[] $chapters
     */
    public function __construct(
        /** Changes whenever the catalogue changes. The app sends it back to skip a download. */
        public string $version,
        public bool $changed,
        public array $exams,
        public array $chapters
    ) {
    }
}
