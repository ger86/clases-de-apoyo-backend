<?php

namespace App\Service\LeadMagnet;

use App\Entity\LeadSubscriber;
use App\Model\Dto\MailDto;
use App\Service\MailerService;
use App\Service\Product\PauBundleProductCatalog;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds the emails of the free exam pack. Every email carries the unsubscribe
 * link in the body and in the List-Unsubscribe headers, which Gmail and Yahoo
 * require for marketing email.
 */
class LeadMailer
{
    /** Subject of each follow-up email, by step number (1-based). */
    public const FOLLOW_UP_SUBJECTS = [
        1 => 'Cómo sacar partido a tus exámenes de la PAU',
        2 => 'Resuelve tus dudas de la PAU al momento',
        3 => 'Todos los exámenes de la PAU de Madrid con soluciones',
    ];

    public function __construct(
        private MailerService $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private PauBundleProductCatalog $productCatalog
    ) {
    }

    public function sendConfirmation(LeadSubscriber $subscriber): void
    {
        $this->send($subscriber, 'email/lead_magnet/confirmation.html.twig', 'Tus exámenes de la PAU de Madrid', [
            'downloadsUrl' => $this->urlGenerator->generate(
                'lead_magnet_downloads',
                ['token' => $subscriber->getConfirmationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    public function sendFollowUp(LeadSubscriber $subscriber, int $step): void
    {
        $subject = self::FOLLOW_UP_SUBJECTS[$step] ?? null;
        if ($subject === null) {
            throw new \InvalidArgumentException(\sprintf('There is no follow-up email %d.', $step));
        }

        $pack = $subscriber->getSourcePackCode() !== null
            ? $this->productCatalog->findByCode($subscriber->getSourcePackCode())
            : null;

        $this->send($subscriber, \sprintf('email/lead_magnet/follow_up_%d.html.twig', $step), $subject, [
            'pack' => $pack,
            'packUrl' => $pack !== null
                ? $this->urlGenerator->generate('product_show', ['slug' => $pack->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                : $this->urlGenerator->generate('product_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // A lead has no account yet, so the button opens the registration. A
            // visitor who is already logged in is sent from there to the payment page.
            'premiumUrl' => $this->urlGenerator->generate('app_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'downloadsUrl' => $this->urlGenerator->generate(
                'lead_magnet_downloads',
                ['token' => $subscriber->getConfirmationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $vars
     */
    private function send(LeadSubscriber $subscriber, string $template, string $subject, array $vars): void
    {
        $unsubscribeUrl = $this->urlGenerator->generate(
            'lead_magnet_unsubscribe',
            ['token' => $subscriber->getUnsubscribeToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        ($this->mailer)(new MailDto(
            template: $template,
            templateVars: $vars + ['unsubscribeUrl' => $unsubscribeUrl],
            subject: $subject,
            to: $subscriber->getEmail(),
            headers: [
                'List-Unsubscribe' => \sprintf('<%s>', $unsubscribeUrl),
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ]
        ));
    }
}
