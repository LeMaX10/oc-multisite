<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes;

use Backend\Facades\BackendAuth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use LeMaX10\MultiSite\Classes\Bridges\DomainBridge;
use LeMaX10\MultiSite\Classes\Bridges\SessionBridge;
use LeMaX10\MultiSite\Classes\Contracts\SiteBridge;
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
     * @var
     */
    private $current;

    /**
     * @var string
     */
    private $mainHost;

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
        $this->mainHost = config('app.url');

        if (App::runningInBackend() && BackendAuth::check()) {
            $this->runBridge(new SessionBridge);
        }

        if (!$this->current) {
            $this->runBridge(new DomainBridge);
        }
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
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Site::active()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function isMain(): bool
    {
        $appDomain = \parse_url($this->mainHost, PHP_URL_HOST);
        $whitlistHosts = [
            'localhost',
            '127.0.0.1',
            $appDomain
        ];

        return \in_array(Request::getHost(), $whitlistHosts, true);
    }

    /**
     * Running Site Bridge and setting current site from manager
     * @param SiteBridge $bridge
     */
    protected function runBridge(SiteBridge $bridge): void
    {
        $this->setSite($bridge->get());
    }
}
