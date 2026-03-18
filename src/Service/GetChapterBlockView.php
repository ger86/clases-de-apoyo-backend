<?php

namespace App\Service;

use App\Entity\Chapter;
use App\Entity\ChapterBlock;
use App\Model\View\ChapterBlockView;

class GetChapterBlockView
{
    public function __construct(private GetChapterView $getChapterView)
    {
    }

    public function __invoke(ChapterBlock $chapterBlock): ChapterBlockView
    {
        return new ChapterBlockView(
            $chapterBlock->getId(),
            $chapterBlock->getName(),
            $chapterBlock->getDescription(),
            $chapterBlock->getWeight(),
            array_map(
                fn (Chapter $chapter) => ($this->getChapterView)($chapter),
                $chapterBlock->getChapters()->toArray()
            )
        );
    }
}
