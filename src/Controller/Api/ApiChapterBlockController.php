<?php

namespace App\Controller\Api;

use App\Entity\ChapterBlock;
use App\Repository\ChapterBlockRepository;
use App\Service\GetChapterBlockView;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ApiChapterBlockController extends AbstractFOSRestController
{

    #[Get(path: '/chapter-blocks')]
    public function getChapterBlocksAction(
        Request $request,
        ChapterBlockRepository $chapterBlockRepository,
        GetChapterBlockView $getChapterBlockView
    ) {
        $ids = $request->query->get('ids');
        if ($ids === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        $chapterBlocks = $chapterBlockRepository->findById(explode(',', $ids));
        return array_map(fn (ChapterBlock $chapterBlock) => ($getChapterBlockView)($chapterBlock), $chapterBlocks);
    }
}
