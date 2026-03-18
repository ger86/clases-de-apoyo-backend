<?php

namespace App\Service;

use App\Entity\Exam;
use App\Entity\File;
use App\Model\View\ExamView;

class GetExamView
{
    public function __construct(private GetFileView $getFileView)
    {
    }

    public function __invoke(Exam $exam): ExamView
    {
        return new ExamView(
            $exam->getId(),
            $exam->getName(),
            $exam->getDescription(),
            $exam->getWeight(),
            $exam->getDifficulty(),
            array_map(
                fn (File $file) => ($this->getFileView)($file),
                $exam->getFiles()->toArray()
            )
        );
    }
}
