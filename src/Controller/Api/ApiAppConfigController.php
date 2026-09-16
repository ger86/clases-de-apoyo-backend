<?php

namespace App\Controller\Api;

use App\Model\View\AppConfigView;
use App\Model\View\AppProductView;
use App\Service\Stripe\StripeCreateCheckoutSession;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ApiAppConfigController extends AbstractFOSRestController
{
    public function __construct(
        #[Autowire('%app.mobile.min_supported_version%')]
        private string $minSupportedVersion,
        #[Autowire('%app.mobile.store_url%')]
        private string $storeUrl,
        #[Autowire('%app.apple.monthly_product_id%')]
        private string $monthlyProductId,
        #[Autowire('%app.apple.yearly_product_id%')]
        private string $yearlyProductId,
        #[Autowire('%app.legal.terms_url%')]
        private string $termsUrl,
        #[Autowire('%app.legal.privacy_url%')]
        private string $privacyUrl,
        #[Autowire('%app.api.gating_enabled%')]
        private bool $gatingEnabled,
        #[Autowire('%app.apple.legacy_access_cutoff%')]
        private string $legacyAccessCutoff
    ) {
    }

    #[Get(path: '/app-config')]
    public function getAppConfigAction(): View
    {
        return $this->view(new AppConfigView(
            $this->minSupportedVersion,
            $this->storeUrl,
            [
                new AppProductView(StripeCreateCheckoutSession::PLAN_MONTHLY, $this->monthlyProductId),
                new AppProductView(StripeCreateCheckoutSession::PLAN_YEARLY, $this->yearlyProductId),
            ],
            $this->termsUrl,
            $this->privacyUrl,
            $this->gatingEnabled,
            $this->legacyAccessCutoffAsAtom()
        ));
    }

    /**
     * The app reads this date to decide who bought the app while it was paid. A value it
     * cannot parse makes it fall back to the app version, so a bad one is passed through
     * rather than breaking the whole configuration.
     */
    private function legacyAccessCutoffAsAtom(): string
    {
        try {
            return (new DateTimeImmutable($this->legacyAccessCutoff))->format(DateTimeInterface::ATOM);
        } catch (Exception) {
            return $this->legacyAccessCutoff;
        }
    }
}
