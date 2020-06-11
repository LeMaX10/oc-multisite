<?php

if (!function_exists('supportedJsonDb')) {
    /**
     * Supported database json data
     * @return bool
     */
    function supportedJsonDb(): bool
    {
        return app(\LeMaX10\MultiSite\Classes\Support\DatabaseSupport::class)
            ->supportJson();
    }
}

if (!function_exists('currentSite')) {
    /**
     * Helper - get current site
     * @return \LeMaX10\MultiSite\Classes\Contracts\Entities\Site|null
     */
    function currentSite(): ?\LeMaX10\MultiSite\Classes\Contracts\Entities\Site
    {
        return app(\LeMaX10\MultiSite\Classes\Contracts\SiteManager::class)
            ->getCurrent();
    }
}

if (!function_exists('siteId')) {
    /**
     * Helper - get current site id
     * @return string|null
     */
    function siteId(): ?string
    {
        return app(\LeMaX10\MultiSite\Classes\Contracts\SiteManager::class)
            ->id();
    }
}
