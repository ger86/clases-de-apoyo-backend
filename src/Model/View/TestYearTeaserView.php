<?php

namespace App\Model\View;

readonly class TestYearTeaserView
{
    /**
     * @param int[] $exams
     */
    public function __construct(
        public int $id,
        public string $year,
        public array $exams
    ) {
    }
}
