<?php

namespace App\Service;

use App\Model\Dto\MailDto;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class MailerService
{

    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        #[Autowire('%app.mail.default_from%')]
        private string $defaultMail,
        #[Autowire('%app.mail.default_name%')]
        private string $defaultMailName
    ) {
    }

    public function __invoke(MailDto $dto): void
    {
        $from = new Address($this->defaultMail, $this->defaultMailName);
        $email = (new TemplatedEmail())
            ->from($dto->from ?? $from)
            ->to($dto->to)
            ->subject($dto->subject)
            ->htmlTemplate($dto->template)
            ->context($dto->templateVars);

        $this->mailer->send($email);
    }
}
