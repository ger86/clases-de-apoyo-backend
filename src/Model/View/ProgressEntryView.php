<?php

namespace App\Model\View;

readonly class ProgressEntryView
{
    public function __construct(
        /** "chapter" or "exam". */
        public string $kind,
        public int $id,
        public string $doneAt
    ) {
    }
}
