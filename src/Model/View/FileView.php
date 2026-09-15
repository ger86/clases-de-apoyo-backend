<?php

namespace App\Model\View;


readonly class FileView
{

    public function __construct(
        public int $id,
        public string $name,
        public ?int $weight,
        /** Null when the file is locked and the client is new enough to show a paywall. */
        public ?string $file,
        public bool $locked
    ) {
    }
}
