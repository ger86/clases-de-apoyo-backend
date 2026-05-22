<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Exam;
use App\Entity\File;
use App\Service\Product\MadridMathPackContext;

class PremiumService
{

    public function __construct(
        private AuthorizationCheckerInterface $authChecker,
        private Security $security,
        private MadridMathPackContext $madridMathPackContext
    )
    {
    }

    public function isPremium(): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $user !== null && $user->isPremium();
    }

    public function canSeeExam(Exam $exam): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $exam->canSee($user);
    }

    public function canSeeChapterFile(File $file): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $file->canSee($user);
    }

    public function canSeeExamFile(File $file): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $exam = $file->getExam();
        if ($exam === null) {
            return $this->canSeeChapterFile($file);
        }

        $user = $this->security->getUser();
        if ($user?->isPremium()) {
            return true;
        }

        if (!$this->madridMathPackContext->supportsExam($exam)) {
            return $exam->canSee($user);
        }

        return $exam->canSee(null) && $this->isFreeExamSampleFile($file);
    }

    public function isMadridMathPackExam(Exam $exam): bool
    {
        return $this->madridMathPackContext->supportsExam($exam);
    }

    public function isMadridMathPackFile(File $file): bool
    {
        return $this->madridMathPackContext->supportsFile($file);
    }

    private function isFreeExamSampleFile(File $file): bool
    {
        return mb_strtolower(trim((string) $file->getName())) === 'enunciados';
    }
}
