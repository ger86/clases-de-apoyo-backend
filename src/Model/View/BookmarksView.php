<?php

namespace App\Model\View;

readonly class BookmarksView
{
    public function __construct(
        /** @var BookmarkView[] */
        public array $bookmarks
    ) {
    }
}
