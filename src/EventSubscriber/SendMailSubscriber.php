<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\SendMailEvent;
use App\Service\MailerService;

class SendMailSubscriber implements EventSubscriberInterface
{

    public function __construct(private MailerService $mailerService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendMailEvent::class => [
                ['sendMail', 10]
            ]
        ];
    }

    public function sendMail(SendMailEvent $event): void
    {
        $dto = $event->dto;
        ($this->mailerService)($dto);
    }
}
