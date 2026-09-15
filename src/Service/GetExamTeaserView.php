<?php

namespace App\Service;

use App\Entity\Exam;
use App\Model\View\ExamTeaserView;

class GetExamTeaserView
{
    public function __construct(private PremiumService $premiumService)
    {
    }

    public function __invoke(Exam $exam): ExamTeaserView
    {
        return new ExamTeaserView(
            (int) $exam->getId(),
            $exam->getName(),
            !$this->premiumService->canSeeExam($exam)
        );
    }
}
