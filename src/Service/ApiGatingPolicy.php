<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Decides whether the API may hide the download link of a locked file.
 *
 * The flag stays off until the new app version is live in the App Store. Even once it is on,
 * a client that does not send X-App-Version keeps getting the link: app 8.3.0 has no idea
 * what a locked file is and would crash on a missing URL. Those installs are pushed to
 * update through the minimum supported version in /api/app-config.
 */
final class ApiGatingPolicy
{
    public const APP_VERSION_HEADER = 'X-App-Version';

    public function __construct(
        #[Autowire('%app.api.gating_enabled%')]
        private bool $gatingEnabled,
        private RequestStack $requestStack
    ) {
    }

    public function shouldHideLockedContent(): bool
    {
        if (!$this->gatingEnabled) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();

        return $request !== null && $request->headers->has(self::APP_VERSION_HEADER);
    }
}
