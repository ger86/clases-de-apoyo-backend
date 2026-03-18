<?php

namespace App\Model\Dto;

use App\Entity\User;

class StripeProcessInvoicePaymentFailedArguments
{

    public function __construct(public readonly User $user, public readonly float $dueAmount)
    {
    }
}
