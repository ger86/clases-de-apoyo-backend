<?php

namespace App\Model\View;

readonly class MeView
{

    public function __construct(
        public int $id,
        public string $email,
        public bool $isVerified,
        public bool $isPremium,
        public ?string $premiumUntil,
        public ?string $premiumProvider,
        /** True when the subscription was bought in the app, so the app may offer to manage it. */
        public bool $canManageInApp,
        /** Passed to StoreKit as appAccountToken so Apple links every transaction to this account. */
        public string $appAccountToken,
        /** True once the account took the free year for having bought the paid app. */
        public bool $legacyAppAccessClaimed
    ) {
    }
}
