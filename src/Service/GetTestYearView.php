<?php

namespace App\Service;

use App\Entity\{Exam, TestYear};
use App\Model\View\TestYearView;

class GetTestYearView
{
    public function __construct(private GetExamView $getExamView)
    {
    }

    public function __invoke(TestYear $testYear): TestYearView
    {
        return new TestYearView(
            $testYear->getId(),
            $testYear->getYear(),
            array_map(
                fn (Exam $e) => ($this->getExamView)($e),
                $testYear->getExams()->toArray()
            )
        );
    }
}
