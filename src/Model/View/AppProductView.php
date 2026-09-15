<?php

namespace App\Model\View;

readonly class AppProductView
{

    public function __construct(
        public string $plan,
        public string $productId
    ) {
    }
}
