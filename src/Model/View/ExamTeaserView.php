<?php

namespace App\Model\View;

readonly class ExamTeaserView
{

    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
