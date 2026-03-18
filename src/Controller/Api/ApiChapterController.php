<?php

namespace App\Controller\Api;

use App\Repository\ChapterRepository;
use App\Service\GetChapterView;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ApiChapterController extends AbstractFOSRestController
{

    #[Get(path: '/chapters/{chapterId}')]
    public function getChapterAction(
        string|int $chapterId,
        ChapterRepository $chapterRepository,
        GetChapterView $getChapterView
    ) {
        $chapter = $chapterRepository->find($chapterId);
        return $this->view(($getChapterView)($chapter));
    }
}
