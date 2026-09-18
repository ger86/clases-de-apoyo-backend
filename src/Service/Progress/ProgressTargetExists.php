<?php

namespace App\Service\Progress;

use App\Model\ProgressKind;
use App\Model\ProgressTarget;
use App\Repository\ChapterRepository;
use App\Repository\ExamRepository;

/** Only real chapters and exams may be marked, so a typo in the app cannot fill the table. */
final class ProgressTargetExists
{
    public function __construct(
        private ChapterRepository $chapterRepository,
        private ExamRepository $examRepository
    ) {
    }

    public function __invoke(ProgressTarget $target): bool
    {
        return match ($target->kind) {
            ProgressKind::Chapter => $this->chapterRepository->find($target->id) !== null,
            ProgressKind::Exam => $this->examRepository->find($target->id) !== null,
        };
    }
}
