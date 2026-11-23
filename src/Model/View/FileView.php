<?php

namespace App\Model\View;


readonly class FileView
{

    public function __construct(
        public int $id,
        public string $name,
        /** The full chapter or exam name, because file names repeat. */
        public ?string $source,
        public ?int $weight,
        /** Null when the file is locked and the client is new enough to show a paywall. */
        public ?string $file,
        public bool $locked
    ) {
    }
}
