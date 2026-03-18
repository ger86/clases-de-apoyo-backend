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
            new TwigFunction('isPremium', [$this, 'isPremium'], [
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

    public function isPremium(): bool
    {
        return $this->premiumService->isPremium();
    }
}
