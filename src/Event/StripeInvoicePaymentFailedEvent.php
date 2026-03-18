<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Stripe\Invoice;

class StripeInvoicePaymentFailedEvent extends Event implements StripeEventInterface
{
    public function __construct(public readonly Invoice $invoice)
    {
    }
}
