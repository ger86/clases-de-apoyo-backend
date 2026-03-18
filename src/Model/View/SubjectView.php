<?php

namespace App\Model\View;

readonly class SubjectView
{

    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
