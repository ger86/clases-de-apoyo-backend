<?php

namespace App\Model;

class MenuLink
{
    /**
     * @param MenuLink[] $children
     */
    public function __construct(
        public readonly string $name,
        public readonly string $link,
        public readonly array $children = []
    ) {
    }
}
