<?php

namespace App\Model\View;

readonly class AppConfigView
{

    /**
     * @param AppProductView[] $iosProducts
     */
    public function __construct(
        public string $minSupportedVersion,
        /** Where the update screen sends a user whose app is older than the minimum. */
        public string $storeUrl,
        public array $iosProducts,
        public string $termsUrl,
        public string $privacyUrl,
        public bool $gatingEnabled,
        /** Anything the Apple ID bought before this date earns the free year. */
        public string $legacyAccessCutoff
    ) {
    }
}
