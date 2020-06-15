<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes\Bridges;

use LeMaX10\MultiSite\Classes\SiteCacheManager;
use LeMaX10\MultiSite\Models\Site;
use Request;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site as SiteContract;
use LeMaX10\MultiSite\Classes\Contracts\SiteBridge;

/**
 * Class DomainBridge
 * @package LeMaX10\MultiSite\Classes\Bridges
 */
class SessionBridge implements SiteBridge
{
    /**
     * @var string|null
     */
    private $siteId;

    /**
     * DomainBridge constructor.
     */
    public function __construct()
    {
        $this->siteId = session('multisite.backend.siteId');
    }

    /**
     * @inheritDoc
     */
    public function get(): ?SiteContract
    {
        if (!$this->siteId) {
            return null;
        }

        return Site::active()->find($this->siteId);
    }
}
