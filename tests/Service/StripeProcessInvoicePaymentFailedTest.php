<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Model\Dto\StripeProcessInvoicePaymentFailedArguments;
use App\Service\Stripe\StripeProcessInvoicePaymentFailed;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Exception\TransportException;

final class StripeProcessInvoicePaymentFailedTest extends TestCase
{
    public function testMailerTransportFailureDoesNotStopWebhookProcessing(): void
    {
        $eventDispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->willThrowException(new TransportException('SMTP credentials rejected.'));

        $service = new StripeProcessInvoicePaymentFailed($eventDispatcher, new NullLogger(), 'admin@example.com');

        $service(new StripeProcessInvoicePaymentFailedArguments(
            (new User())->setEmail('student@example.com'),
            5.0
        ));
    }
}
