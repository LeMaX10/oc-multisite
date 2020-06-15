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
class DomainBridge implements SiteBridge
{
    /**
     * @var string
     */
    private $domain;
    /**
     * @var string
     */
    private $schema;

    /**
     * @var SiteCacheManager
     */
    private $cacheManager;

    /**
     * DomainBridge constructor.
     */
    public function __construct()
    {
        $this->domain = Request::getHost();
        $this->schema = Request::getScheme();

        $this->cacheManager = new SiteCacheManager;
    }

    /**
     * @inheritDoc
     */
    public function get(): ?SiteContract
    {
        if (!$this->domain) {
            return null;
        }

        $site = $this->getFromCache($this->domain);
        if (!$site) {
            $site = $this->getFromDatabase($this->domain);
        }

        return $site;
    }

    /**
     * @param string $domain
     * @return Site|null
     */
    protected function getFromCache(string $domain): ?Site
    {
        $siteKeyInMemory = $this->cacheManager->getByDomain($this->domain);
        if (!$siteKeyInMemory) {
            return null;
        }

        return Site::active()->find($siteKeyInMemory);
    }

    /**
     * @param string $domain
     * @return Site|null
     */
    protected function getFromDatabase(string $domain): ?Site
    {
        try {
            $site = Site::active()->findByDomain($this->domain)->firstOrFail();
            $this->cacheManager->put($this->domain, $site->getKey());

            return $site;
        } catch (\Exception $e) {
            return null;
        }
    }
}
