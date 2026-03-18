<?php

namespace App\Model;

class Breadcrumb
{
    public function __construct(public readonly string $name, public readonly ?string $link)
    {
    }
}
