<?php

namespace App\Enum;

class StripeConsts
{

    const HTTP_STRIPE_SIGNATURE = 'HTTP_STRIPE_SIGNATURE';
    const CHECKOUT_SESSION_COMPLETED = 'checkout.session.completed';
    const INVOICE_PAYMENT_SUCCEEDED = 'invoice.paid';
    const INVOICE_PAYMENT_FAILED = 'invoice.payment_failed';
    const CUSTOMER_SUBSCRIPTION_UPDATED = 'customer.subscription.updated';
}
