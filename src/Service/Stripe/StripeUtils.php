<?php

namespace App\Service\Stripe;

final class StripeUtils
{
    public function convertToStringAmount(float $amount): string
    {
        $amountRounded = round($amount, 2);
        return \strval(\intval($amountRounded * 100));
    }
}
