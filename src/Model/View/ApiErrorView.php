<?php

namespace App\Model\View;

readonly class ApiErrorView
{

    public function __construct(
        public string $message,
        public string $code
    ) {
    }
}
