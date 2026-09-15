<?php

namespace App\Service\Apple;

use Readdle\AppStoreServerAPI\AppStoreServerAPI;
use Readdle\AppStoreServerAPI\Environment;
use Readdle\AppStoreServerAPI\Exception\AppStoreServerAPIException;
use Readdle\AppStoreServerAPI\Response\StatusResponse;
use Readdle\AppStoreServerAPI\Util\Helper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Talks to the App Store Server API and holds the Apple root certificate used to
 * check the signature of everything Apple sends us.
 */
final class AppleClient
{
    /** @var array<string,AppStoreServerAPI> */
    private array $apis = [];

    private ?string $rootCertificate = null;

    public function __construct(
        #[Autowire('%app.apple.issuer_id%')]
        private string $issuerId,
        #[Autowire('%app.apple.bundle_id%')]
        private string $bundleId,
        #[Autowire('%app.apple.key_id%')]
        private string $keyId,
        #[Autowire('%app.apple.private_key_path%')]
        private string $privateKeyPath,
        #[Autowire('%app.apple.environment%')]
        private string $environment,
        #[Autowire('%app.apple.root_certificate_path%')]
        private string $rootCertificatePath
    ) {
    }

    public function getBundleId(): string
    {
        return $this->bundleId;
    }

    /**
     * A build from TestFlight produces sandbox transactions even when the server is set to
     * production, so both environments are tried before giving up.
     *
     * @throws AppStoreServerAPIException
     */
    public function getAllSubscriptionStatuses(string $transactionId): StatusResponse
    {
        $lastException = null;

        foreach ($this->environments() as $environment) {
            try {
                return $this->api($environment)->getAllSubscriptionStatuses($transactionId);
            } catch (AppStoreServerAPIException $exception) {
                $lastException = $exception;
            }
        }

        throw $lastException ?? new AppleConfigurationException('No Apple environment is configured.');
    }

    public function getRootCertificate(): string
    {
        if ($this->rootCertificate !== null) {
            return $this->rootCertificate;
        }

        $binary = @file_get_contents($this->rootCertificatePath);
        if ($binary === false) {
            throw new AppleConfigurationException(
                \sprintf('Apple root certificate could not be read from "%s".', $this->rootCertificatePath)
            );
        }

        return $this->rootCertificate = Helper::toPEM($binary);
    }

    /**
     * @return string[]
     */
    private function environments(): array
    {
        return $this->environment === Environment::PRODUCTION
            ? [Environment::PRODUCTION, Environment::SANDBOX]
            : [Environment::SANDBOX, Environment::PRODUCTION];
    }

    private function api(string $environment): AppStoreServerAPI
    {
        if (isset($this->apis[$environment])) {
            return $this->apis[$environment];
        }

        if ($this->issuerId === '' || $this->keyId === '' || $this->privateKeyPath === '') {
            throw new AppleConfigurationException(
                'Apple in-app purchase is not configured: APPLE_ISSUER_ID, APPLE_KEY_ID and APPLE_PRIVATE_KEY_PATH are required.'
            );
        }

        $privateKey = @file_get_contents($this->privateKeyPath);
        if ($privateKey === false) {
            throw new AppleConfigurationException(
                \sprintf('Apple private key could not be read from "%s".', $this->privateKeyPath)
            );
        }

        return $this->apis[$environment] = new AppStoreServerAPI(
            $environment,
            $this->issuerId,
            $this->bundleId,
            $this->keyId,
            $privateKey
        );
    }
}
