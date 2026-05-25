<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\CommunityTestCourseSubject;
use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\File;
use App\Entity\Product;
use App\Service\Product\PauBundleProductCatalog;
use App\Service\Product\PauBundleProductDefinition;

class PremiumService
{

    public function __construct(
        private AuthorizationCheckerInterface $authChecker,
        private Security $security,
        private PauBundleProductCatalog $pauBundleProductCatalog
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

        if ($this->pauBundleProductCatalog->findByExam($exam) === null) {
            return $exam->canSee($user);
        }

        if ($this->isFreeExamSampleFile($file)) {
            return true;
        }

        return false;
    }

    public function isMadridMathPackExam(Exam $exam): bool
    {
        return $this->pauBundleProductCatalog->findByExam($exam) !== null;
    }

    public function isMadridMathPackFile(File $file): bool
    {
        return $this->pauBundleProductCatalog->findByFile($file) !== null;
    }

    public function getPauBundlePackForExam(Exam $exam): ?PauBundleProductDefinition
    {
        return $this->pauBundleProductCatalog->findByExam($exam);
    }

    public function getPauBundlePackForFile(File $file): ?PauBundleProductDefinition
    {
        return $this->pauBundleProductCatalog->findByFile($file);
    }

    public function getPauBundlePackForCommunityTestCourseSubject(CommunityTestCourseSubject $communityTestCourseSubject): ?PauBundleProductDefinition
    {
        return $this->pauBundleProductCatalog->findByCommunityTestCourseSubject($communityTestCourseSubject);
    }

    public function getPauBundlePackForProduct(Product $product): ?PauBundleProductDefinition
    {
        return $this->pauBundleProductCatalog->findByProduct($product);
    }

    public function getPauBundlePackForCourseSubject(CourseSubject $courseSubject): ?PauBundleProductDefinition
    {
        return $this->pauBundleProductCatalog->findByCourseSubject($courseSubject);
    }

    private function isFreeExamSampleFile(File $file): bool
    {
        return str_starts_with(mb_strtolower(trim((string) $file->getName())), 'enunciado');
    }
}
