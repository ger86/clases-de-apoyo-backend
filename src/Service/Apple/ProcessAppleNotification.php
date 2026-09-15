<?php

namespace App\Service\Apple;

use App\Entity\AppleNotification;
use App\Entity\User;
use App\Repository\AppleNotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Readdle\AppStoreServerAPI\ResponseBodyV2;
use Readdle\AppStoreServerAPI\TransactionInfo;

/**
 * Receives App Store Server Notifications V2. Without this endpoint a renewal would
 * never reach us and a paying user would lose access at the end of their first period.
 */
final class ProcessAppleNotification
{
    private const REVOKING_TYPES = [
        ResponseBodyV2::NOTIFICATION_TYPE__REFUND,
        ResponseBodyV2::NOTIFICATION_TYPE__REVOKE,
    ];

    public function __construct(
        private AppleClient $appleClient,
        private AppleSubscriptionStateApplier $applier,
        private AppleNotificationRepository $appleNotificationRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @throws \Readdle\AppStoreServerAPI\Exception\AppStoreServerNotificationException
     */
    public function __invoke(string $rawBody): void
    {
        $notification = ResponseBodyV2::createFromRawNotification(
            $rawBody,
            $this->appleClient->getRootCertificate()
        );

        // Apple retries a notification until it gets a 200, so the same one arrives more than once.
        if ($this->appleNotificationRepository->findOneByUuid($notification->getNotificationUUID()) !== null) {
            return;
        }

        $appMetadata = $notification->getAppMetadata();
        $transactionInfo = $appMetadata->getTransactionInfo();

        $record = new AppleNotification(
            $notification->getNotificationUUID(),
            $notification->getNotificationType(),
            $notification->getSubtype(),
            $transactionInfo?->getOriginalTransactionId(),
            $appMetadata->getEnvironment()
        );

        $this->em->persist($record);
        $record->setNote($this->applyTo($notification, $transactionInfo, $appMetadata->getBundleId()));

        $this->em->flush();
    }

    private function applyTo(
        ResponseBodyV2 $notification,
        ?TransactionInfo $transactionInfo,
        ?string $bundleId
    ): ?string {
        if ($notification->getNotificationType() === ResponseBodyV2::NOTIFICATION_TYPE__TEST) {
            return 'test notification, nothing applied';
        }

        if ($transactionInfo === null) {
            return 'no transaction info in the notification';
        }

        if ($bundleId !== null && $bundleId !== $this->appleClient->getBundleId()) {
            return 'bundle id does not belong to this app: ' . $bundleId;
        }

        $user = $this->findUser($transactionInfo);
        if ($user === null) {
            // The purchase may not have been confirmed by the app yet, or the account was deleted.
            return 'no account matches this transaction';
        }

        if (\in_array($notification->getNotificationType(), self::REVOKING_TYPES, true)) {
            $this->applier->revoke($user);

            return 'premium revoked for user ' . $user->getId();
        }

        $this->applier->apply($user, $transactionInfo, $notification->getAppMetadata()->getRenewalInfo());

        return 'premium applied for user ' . $user->getId();
    }

    private function findUser(TransactionInfo $transactionInfo): ?User
    {
        $user = $this->userRepository->findOneByAppleOriginalTransactionId(
            $transactionInfo->getOriginalTransactionId()
        );

        if ($user !== null) {
            return $user;
        }

        $appAccountToken = $transactionInfo->getAppAccountToken();
        if ($appAccountToken === null || $appAccountToken === '') {
            return null;
        }

        return $this->userRepository->findOneByAppleAppAccountToken($appAccountToken);
    }
}
