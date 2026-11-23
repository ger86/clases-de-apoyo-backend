<?php

namespace App\Model\View;

readonly class BookmarkView
{
    public function __construct(
        public int $fileId,
        public string $name,
        /** The chapter or exam the file belongs to, because file names repeat. */
        public ?string $source,
        /** Same rule as FileView: true when the file needs a subscription. */
        public bool $locked,
        public string $bookmarkedAt
    ) {
    }
}
