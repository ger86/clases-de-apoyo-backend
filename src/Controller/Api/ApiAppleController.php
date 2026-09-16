<?php

namespace App\Controller\Api;

use App\Model\View\ApiErrorView;
use App\Service\Apple\AppleConfigurationException;
use App\Service\Apple\ClaimLegacyAppAccess;
use App\Service\Apple\AppleTransactionException;
use App\Service\Apple\ProcessAppleNotification;
use App\Service\Apple\ProcessAppleTransaction;
use App\Service\GetMeView;
use App\Service\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use DateTimeImmutable;
use FOS\RestBundle\View\View;
use Psr\Log\LoggerInterface;
use Readdle\AppStoreServerAPI\Exception\AppStoreServerNotificationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ApiAppleController extends AbstractFOSRestController
{
    /**
     * Confirms a purchase made in the app, and also runs when the user restores purchases.
     */
    #[Post(path: '/apple/transactions')]
    #[IsGranted('ROLE_USER')]
    public function postTransactionAction(
        Request $request,
        Security $security,
        ProcessAppleTransaction $processAppleTransaction,
        GetMeView $getMeView,
        LoggerInterface $logger,
        #[Autowire(service: 'limiter.api_apple_transaction')]
        RateLimiterFactoryInterface $apiAppleTransactionLimiter
    ): View {
        $user = $security->getSafeUser();

        if (!$apiAppleTransactionLimiter->create((string) $user->getId())->consume()->isAccepted()) {
            return $this->view(
                new ApiErrorView('Demasiados intentos. Inténtalo de nuevo más tarde.', 'too_many_requests'),
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $transactionId = trim((string) $request->getPayload()->get('transactionId'));
        if ($transactionId === '') {
            return $this->view(
                new ApiErrorView('Falta el identificador de la compra.', 'missing_transaction_id'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            ($processAppleTransaction)($user, $transactionId);
        } catch (AppleTransactionException $exception) {
            return $this->view(
                new ApiErrorView($exception->getMessage(), $exception->errorCode),
                $exception->statusCode
            );
        } catch (AppleConfigurationException $exception) {
            $logger->error('Apple in-app purchase is not configured', ['exception' => $exception->getMessage()]);

            return $this->view(
                new ApiErrorView('Las compras no están disponibles ahora mismo.', 'apple_not_configured'),
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        }

        return $this->view(($getMeView)($user));
    }

    /**
     * Gives the free year to an account whose Apple ID bought the app when it was paid.
     *
     * The app reads the original purchase from StoreKit and sends it here. The date is not
     * verified against Apple, so the only thing this grants is one year on one account.
     */
    #[Post(path: '/apple/legacy-access')]
    #[IsGranted('ROLE_USER')]
    public function postLegacyAccessAction(
        Request $request,
        Security $security,
        ClaimLegacyAppAccess $claimLegacyAppAccess,
        GetMeView $getMeView
    ): View {
        $user = $security->getSafeUser();
        $originalPurchaseDate = $request->getPayload()->get('originalPurchaseDate');

        if (!is_numeric($originalPurchaseDate)) {
            return $this->view(
                new ApiErrorView('Falta la fecha de compra original.', 'missing_original_purchase_date'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $appTransactionId = $request->getPayload()->get('appTransactionId');

        try {
            ($claimLegacyAppAccess)(
                $user,
                // Apple reports every date as milliseconds since the epoch.
                (new DateTimeImmutable())->setTimestamp(intdiv((int) $originalPurchaseDate, 1000)),
                $appTransactionId === null ? null : (string) $appTransactionId
            );
        } catch (AppleTransactionException $exception) {
            return $this->view(
                new ApiErrorView($exception->getMessage(), $exception->errorCode),
                $exception->statusCode
            );
        }

        return $this->view(($getMeView)($user));
    }

    /**
     * App Store Server Notifications V2. Apple signs the body, so no other authentication is used.
     */
    #[Post(path: '/apple/notifications')]
    public function postNotificationAction(
        Request $request,
        ProcessAppleNotification $processAppleNotification,
        LoggerInterface $logger
    ): View {
        try {
            ($processAppleNotification)($request->getContent());
        } catch (AppStoreServerNotificationException | AppleConfigurationException $exception) {
            $logger->warning('Apple notification rejected', ['exception' => $exception->getMessage()]);

            return $this->view(
                new ApiErrorView('La notificación no se ha podido verificar.', 'invalid_notification'),
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->view(null, Response::HTTP_OK);
    }
}
