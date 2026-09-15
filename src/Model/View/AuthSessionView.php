<?php

namespace App\Model\View;

readonly class AuthSessionView
{

    public function __construct(
        public string $token,
        public MeView $user
    ) {
    }
}
