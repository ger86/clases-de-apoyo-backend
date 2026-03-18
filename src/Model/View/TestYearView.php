<?php

namespace App\Model\View;

readonly class TestYearView
{
    /**
     * @param ExamTeaserView[] $exams
     */
    public function __construct(
        public int $id,
        public string $year,
        public array $exams
    ) {
    }
}
