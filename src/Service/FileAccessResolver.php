<?php

namespace App\Service;

use App\Entity\File;
use App\Repository\FileRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileAccessResolver
{
    public function __construct(
        private FileRepository $fileRepository,
        private PremiumService $premiumService
    ) {
    }

    public function resolveOrThrow(string $fileId): File
    {
        $file = $this->fileRepository->find($fileId);
        if ($file === null) {
            throw new NotFoundHttpException('No existe ese archivo');
        }

        $exam = $file->getExam();
        if ($exam !== null && !$this->premiumService->canSeeExam($exam)) {
            throw new AccessDeniedHttpException('No puedes ver ese archivo');
        }

        $chapter = $file->getChapter();
        if ($chapter !== null && !$this->premiumService->canSeeChapterFile($file)) {
            throw new AccessDeniedHttpException('No puedes ver ese archivo');
        }

        return $file;
    }
}
