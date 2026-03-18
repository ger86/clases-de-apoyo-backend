<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Stripe\Invoice;

class StripeInvoicePaymentSucceededEvent extends Event  implements StripeEventInterface
{

    public function __construct(public readonly Invoice $invoice)
    {
    }
}
