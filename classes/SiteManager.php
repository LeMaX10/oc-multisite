<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes;

use Backend\Facades\BackendAuth;
use LeMaX10\MultiSite\Classes\Policies\BackendUserPolicy;
use October\Rain\Support\Facades\Config;
use October\Rain\Support\Traits\Singleton;
use Request;
use LeMaX10\MultiSite\Models\Site;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager as SiteManagerContract;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site as SiteEntityContract;

/**
 * Class SiteManager
 * @package LeMaX10\MultiSite\Classes
 */
class SiteManager implements SiteManagerContract
{
    use Singleton;

    /**
     * @var string
     */
    private $domain;
    /**
     * @var string
     */
    private $schema;
    /**
     * @var
     */
    private $current;

    /**
     * @var SiteCacheManager
     */
    private $cacheManager;

    /**
     * @var SiteConfiguration|null
     */
    private $configuration;

    /**
     * SiteManager constructor.
     */
    public function init()
    {
        $this->domain = Request::getHost();
        $this->schema = Request::getScheme();
        $this->cacheManager = new SiteCacheManager;

        $this->detectionByHost();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrent(): ?SiteEntityContract
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function id(): ?string
    {
        return optional($this->getCurrent())->getKey();
    }

    /**
     * @return SiteConfiguration|null
     */
    public function getConfiguration(): ?SiteConfiguration
    {
        return $this->configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function setSite(?SiteEntityContract $site): SiteManagerContract
    {
        $this->current = $site;
        if ($this->current) {
            $this->configuration = new SiteConfiguration($this->current);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSiteById(string $id): SiteManagerContract
    {
        $site = Site::active()->find($id);
        return $this->setSite($site);
    }

    /**
     * {@inheritDoc}
     */
    public function detectionByHost(): void
    {
        if (!$this->domain) {
            return;
        }

        $siteKeyInMemory = $this->cacheManager->getByDomain($this->domain);
        if (!$siteKeyInMemory) {
            $site = Site::active()
                ->findByDomain($this->domain)
                ->first();

            if ($site) {
                $this->cacheManager->put($this->domain, $site->getKey());
            }
        } else {
            $site = Site::active()->find($siteKeyInMemory);
        }

        $this->setSite($site);
    }

    /**
     * {@inheritDoc}
     */
    public function access(): bool
    {
        if (!$this->getCurrent()->isProtected()) {
            return true;
        }

        $policy = false;
        $backendUser = BackendAuth::getUser();
        if ($backendUser) {
            $policy = new BackendUserPolicy;
        }

        return $policy && $policy->view($backendUser, $this->getCurrent());
    }

    /**
     * {@inheritDoc}
     */
    public function mainDomain(): bool
    {
        $appDomain = \parse_url(config('app.url'), PHP_URL_HOST);
        return \in_array($this->domain, ['localhost', '127.0.0.1', $appDomain], true);
    }
}
