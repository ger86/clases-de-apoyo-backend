<?php

namespace App\Controller;

use App\Service\LeadMagnet\FreeExamPack;
use App\Service\LeadMagnet\LeadSubscriptionManager;
use App\Service\Product\ProductDownloadStorage;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The free exam pack: a visitor leaves an email, receives a link, and the link
 * confirms the address and opens the downloads. See docs/runbooks/free-exam-pack.md.
 */
final class LeadMagnetController extends AbstractController
{
    /** Name of the hidden field that only bots fill in. */
    private const HONEYPOT_FIELD = 'website';

    /**
     * Off until the PDFs are in S3 (LEAD_MAGNET_ENABLED). Downloads, unsubscribe
     * and follow-ups keep working when it is off, for the people already signed up.
     */
    public function __construct(
        #[Autowire('%app.lead_magnet.enabled%')]
        private bool $enabled
    ) {
    }

    public function landing(FreeExamPack $freeExamPack): Response
    {
        $this->denyWhenDisabled();

        return $this->render('views/lead_magnet/landing.html.twig', [
            'files' => $freeExamPack->getFiles(),
            'errors' => [],
            'email' => '',
            'packCode' => null,
        ]);
    }

    public function subscribe(
        Request $request,
        FreeExamPack $freeExamPack,
        LeadSubscriptionManager $subscriptionManager,
        ValidatorInterface $validator,
        #[Autowire(service: 'limiter.lead_magnet_subscribe')]
        RateLimiterFactoryInterface $subscribeLimiter
    ): Response {
        $this->denyWhenDisabled();

        // Bots fill every field; people never see this one. Answer as if all went well.
        if (trim((string) $request->request->get(self::HONEYPOT_FIELD)) !== '') {
            return $this->redirectToRoute('lead_magnet_check_email');
        }

        $email = trim((string) $request->request->get('email'));
        $errors = [];
        if (\count($validator->validate($email, [new Assert\NotBlank(), new Assert\Email(), new Assert\Length(max: 180)])) > 0) {
            $errors[] = 'Escribe un email válido.';
        }
        if ($request->request->get('consent') !== '1') {
            $errors[] = 'Marca la casilla para aceptar que te enviemos los exámenes y nuestros consejos por email.';
        }

        if ($errors === [] && !$subscribeLimiter->create((string) $request->getClientIp())->consume()->isAccepted()) {
            $errors[] = 'Has hecho muchas solicitudes seguidas. Espera un rato y vuelve a intentarlo.';
        }

        $packCode = $request->request->get('pack');
        $packCode = \is_string($packCode) && $packCode !== '' ? $packCode : null;

        if ($errors === []) {
            try {
                $subscriptionManager->subscribe(
                    $email,
                    $packCode,
                    $request->getClientIp(),
                    new DateTimeImmutable()
                );

                return $this->redirectToRoute('lead_magnet_check_email');
            } catch (TransportExceptionInterface) {
                $errors[] = 'No hemos podido enviarte el email. Inténtalo de nuevo en unos minutos.';
            }
        }

        return $this->render('views/lead_magnet/landing.html.twig', [
            'files' => $freeExamPack->getFiles(),
            'errors' => $errors,
            'email' => $email,
            // Kept so a student who fixes a typo still gets the follow-ups of the right pack.
            'packCode' => $packCode,
        ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    public function checkEmail(): Response
    {
        $this->denyWhenDisabled();

        return $this->render('views/lead_magnet/check_email.html.twig');
    }

    public function downloads(
        string $token,
        FreeExamPack $freeExamPack,
        LeadSubscriptionManager $subscriptionManager
    ): Response {
        $subscriber = $subscriptionManager->confirm($token, new DateTimeImmutable());
        if ($subscriber === null) {
            throw $this->createNotFoundException('Este enlace no es válido.');
        }

        return $this->render('views/lead_magnet/downloads.html.twig', [
            'token' => $token,
            'files' => $freeExamPack->getFiles(),
        ]);
    }

    public function download(
        string $token,
        string $fileKey,
        FreeExamPack $freeExamPack,
        LeadSubscriptionManager $subscriptionManager,
        ProductDownloadStorage $downloadStorage
    ): RedirectResponse {
        if ($subscriptionManager->confirm($token, new DateTimeImmutable()) === null) {
            throw $this->createNotFoundException('Este enlace no es válido.');
        }

        $file = $freeExamPack->findFile($fileKey);
        if ($file === null) {
            throw $this->createNotFoundException('No existe ese archivo.');
        }

        $downloadUrl = $downloadStorage->createDownloadUrl($file);
        if ($downloadUrl === null) {
            throw $this->createNotFoundException('El archivo todavía no está disponible.');
        }

        return new RedirectResponse($downloadUrl);
    }

    /**
     * GET shows a button, so a mail scanner that opens the link does not
     * unsubscribe anybody. POST unsubscribes: the button, and the one-click
     * unsubscribe of Gmail and Yahoo (RFC 8058), which sends no CSRF token.
     */
    public function unsubscribe(string $token, Request $request, LeadSubscriptionManager $subscriptionManager): Response
    {
        if ($request->isMethod('POST')) {
            if (!$subscriptionManager->unsubscribe($token, new DateTimeImmutable())) {
                throw $this->createNotFoundException('Este enlace no es válido.');
            }

            return $this->render('views/lead_magnet/unsubscribe.html.twig', [
                'token' => $token,
                'done' => true,
            ]);
        }

        $subscriber = $subscriptionManager->findByUnsubscribeToken($token);
        if ($subscriber === null) {
            throw $this->createNotFoundException('Este enlace no es válido.');
        }

        return $this->render('views/lead_magnet/unsubscribe.html.twig', [
            'token' => $token,
            'done' => $subscriber->isUnsubscribed(),
        ]);
    }

    private function denyWhenDisabled(): void
    {
        if (!$this->enabled) {
            throw $this->createNotFoundException('Esta página no está disponible.');
        }
    }
}
