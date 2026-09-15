<?php

namespace App\Enum;

class PremiumProvider
{

    const STRIPE = 'stripe';
    const APPLE = 'apple';
    const MANUAL = 'manual';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [self::STRIPE, self::APPLE, self::MANUAL];
    }
}
