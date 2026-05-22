<?php

namespace App\TwigExtension;

use App\Entity\CommunityTestCourseSubject;
use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\File;
use App\Entity\Product;
use App\Service\PremiumService;
use App\Service\Product\PauBundleProductDefinition;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PremiumExtension extends AbstractExtension
{

    public function __construct(private PremiumService $premiumService)
    {
    }

    public function getName()
    {
        return 'premiumUser';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('canSeeChapterFile', [$this, 'canSeeChapterFile'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('canSeeExam', [$this, 'canSeeExam'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('canSeeExamFile', [$this, 'canSeeExamFile'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('isPremium', [$this, 'isPremium'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('isMadridMathPackExam', [$this, 'isMadridMathPackExam'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('isMadridMathPackFile', [$this, 'isMadridMathPackFile'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('getPauBundlePackForExam', [$this, 'getPauBundlePackForExam'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('getPauBundlePackForFile', [$this, 'getPauBundlePackForFile'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('getPauBundlePackForCommunityTestCourseSubject', [$this, 'getPauBundlePackForCommunityTestCourseSubject'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('getPauBundlePackForProduct', [$this, 'getPauBundlePackForProduct'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ]),
            new TwigFunction('getPauBundlePackForCourseSubject', [$this, 'getPauBundlePackForCourseSubject'], [
                'is_safe' => ['html'],
                'needs_environment' => false
            ])
        ];
    }

    public function canSeeChapterFile(File $file): bool
    {
        return $this->premiumService->canSeeChapterFile($file);
    }

    public function canSeeExam(Exam $exam): bool
    {
        return $this->premiumService->canSeeExam($exam);
    }

    public function canSeeExamFile(File $file): bool
    {
        return $this->premiumService->canSeeExamFile($file);
    }

    public function isPremium(): bool
    {
        return $this->premiumService->isPremium();
    }

    public function isMadridMathPackExam(Exam $exam): bool
    {
        return $this->premiumService->isMadridMathPackExam($exam);
    }

    public function isMadridMathPackFile(File $file): bool
    {
        return $this->premiumService->isMadridMathPackFile($file);
    }

    public function getPauBundlePackForExam(Exam $exam): ?PauBundleProductDefinition
    {
        return $this->premiumService->getPauBundlePackForExam($exam);
    }

    public function getPauBundlePackForFile(File $file): ?PauBundleProductDefinition
    {
        return $this->premiumService->getPauBundlePackForFile($file);
    }

    public function getPauBundlePackForCommunityTestCourseSubject(CommunityTestCourseSubject $communityTestCourseSubject): ?PauBundleProductDefinition
    {
        return $this->premiumService->getPauBundlePackForCommunityTestCourseSubject($communityTestCourseSubject);
    }

    public function getPauBundlePackForProduct(Product $product): ?PauBundleProductDefinition
    {
        return $this->premiumService->getPauBundlePackForProduct($product);
    }

    public function getPauBundlePackForCourseSubject(CourseSubject $courseSubject): ?PauBundleProductDefinition
    {
        return $this->premiumService->getPauBundlePackForCourseSubject($courseSubject);
    }
}
