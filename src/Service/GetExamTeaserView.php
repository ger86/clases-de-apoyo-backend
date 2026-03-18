<?php

namespace App\Service;

use App\Entity\Exam;
use App\Model\View\ExamTeaserView;

class GetExamTeaserView
{

    public function __invoke(Exam $exam): ExamTeaserView
    {
        return new ExamTeaserView(
            $exam->getId(),
            $exam->getName()
        );
    }
}
