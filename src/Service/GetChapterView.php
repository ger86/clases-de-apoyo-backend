<?php

namespace App\Service;

use App\Entity\Chapter;
use App\Entity\File;
use App\Model\View\ChapterView;

class GetChapterView
{
    public function __construct(private GetFileView $getFileView)
    {
    }

    public function __invoke(Chapter $chapter): ChapterView
    {
        return new ChapterView(
            $chapter->getId(),
            $chapter->getName(),
            $chapter->getDescription(),
            $chapter->getWeight(),
            array_map(
                fn (File $file) => ($this->getFileView)($file),
                $chapter->getFiles()->toArray()
            )
        );
    }
}
