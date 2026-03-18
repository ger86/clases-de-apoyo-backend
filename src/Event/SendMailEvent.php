<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Model\Dto\MailDto;

final class SendMailEvent extends Event
{
    public function __construct(public readonly MailDto $dto)
    {
    }
}
