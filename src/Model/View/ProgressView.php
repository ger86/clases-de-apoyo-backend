<?php

namespace App\Model\View;

readonly class ProgressView
{
    public function __construct(
        /** @var ProgressEntryView[] */
        public array $done
    ) {
    }
}
