<?php

namespace App\Service;

use App\Entity\File;
use App\Model\View\FileView;

class GetFileView
{
    public function __construct(
        private PublicUrlGenerator $publicUrlGenerator
    ) {
    }

    public function __invoke(File $file): FileView
    {
        $url = $this->publicUrlGenerator->generate($file->getFile());
        return new FileView(
            $file->getId(),
            $file->getName(),
            $file->getWeight(),
            $url
        );
    }
}
