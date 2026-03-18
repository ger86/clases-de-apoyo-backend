<?php

namespace App\Controller;

use App\Repository\FileRepository;
use App\Service\PremiumService;
use App\Service\PublicUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FileController extends AbstractController
{
    public function viewer(
        string $fileId,
        FileRepository $fileRepository,
        PremiumService $premiumService,
        PublicUrlGenerator $publicUrlGenerator
    ): Response {
        $file = $fileRepository->find($fileId);
        if ($file === null) {
            throw $this->createNotFoundException('No existe ese archivo');
        }
        $exam = $file->getExam();
        if ($exam !== null && !$premiumService->canSeeExam($exam)) {
            throw $this->createAccessDeniedException('No puedes ver ese archivo');
        }
        $chapter = $file->getChapter();
        if ($chapter !== null && !$premiumService->canSeeChapterFile($file)) {
            throw $this->createAccessDeniedException('No puedes ver ese archivo');
        }
        return $this->render('views/files/viewer.html.twig', [
            'file' => $file,
            'url' => $publicUrlGenerator->generate($file->getFile())
        ]);
    }
}
