<?php

namespace App\Service;

use App\Entity\File;
use App\Model\View\FileView;

class GetFileView
{
    public function __construct(
        private PublicUrlGenerator $publicUrlGenerator,
        private PremiumService $premiumService,
        private ApiGatingPolicy $apiGatingPolicy
    ) {
    }

    public function __invoke(File $file): FileView
    {
        // canSeeExamFile falls back to the chapter rules when the file has no exam,
        // so the API and the website answer with exactly the same rules.
        $locked = !$this->premiumService->canSeeExamFile($file);
        $hideUrl = $locked && $this->apiGatingPolicy->shouldHideLockedContent();

        return new FileView(
            (int) $file->getId(),
            $file->getName(),
            $file->getWeight(),
            $hideUrl ? null : $this->publicUrlGenerator->generate($file->getFile()),
            $locked
        );
    }
}
