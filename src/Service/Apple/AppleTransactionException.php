<?php

namespace App\Service\Apple;

use RuntimeException;

final class AppleTransactionException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $statusCode
    ) {
        parent::__construct($message);
    }
}
