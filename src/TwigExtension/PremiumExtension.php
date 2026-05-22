<?php

namespace App\TwigExtension;

use App\Entity\Exam;
use App\Entity\File;
use App\Service\PremiumService;
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
}
