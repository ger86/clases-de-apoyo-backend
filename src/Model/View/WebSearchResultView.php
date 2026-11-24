<?php

namespace App\Model\View;

/**
 * One row on the website search page: an exam or a chapter, with the link to its page.
 */
final readonly class WebSearchResultView
{
    public const TYPE_EXAM = 'exam';

    public const TYPE_CHAPTER = 'chapter';

    public function __construct(
        public string $type,
        public string $title,
        public string $context,
        public ?string $description,
        public string $url
    ) {
    }
}
