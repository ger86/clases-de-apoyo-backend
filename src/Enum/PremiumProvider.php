<?php

namespace App\Enum;

class PremiumProvider
{

    const STRIPE = 'stripe';
    const APPLE = 'apple';
    const MANUAL = 'manual';
    /** The free year given to people who had already bought the app when it was paid. */
    const LEGACY_APP = 'legacy_app';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [self::STRIPE, self::APPLE, self::MANUAL, self::LEGACY_APP];
    }
}
