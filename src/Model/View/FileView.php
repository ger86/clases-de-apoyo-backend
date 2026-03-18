<?php

namespace App\Model\View;


readonly class FileView
{

    public function __construct(
        public int $id,
        public string $name,
        public ?int $weight,
        public string $file
    ) {
    }
}
