<?php

namespace App\Model\Dto;

readonly class MailDto
{

    public function __construct(
        public string $template,
        public array $templateVars,
        public string $subject,
        public string $to,
        public ?array $from = null
    ) {
    }
}
