<?php namespace LeMaX10\MultiSite\Classes;


use Backend\Facades\BackendAuth;
use October\Rain\Support\Facades\Config;
use Request;
use LeMaX10\MultiSite\Models\Site;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager as SiteManagerContract;
use LeMaX10\MultiSite\Classes\Contracts\Site as SiteContract;

/**
 * Class SiteManager
 * @package LeMaX10\MultiSite\Classes
 */
class SiteManager implements SiteManagerContract
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
     * @var
     */
    private $current;

    /**
     * @var SiteCacheManager
     */
    private $cacheManager;

    /**
     * SiteManager constructor.
     */
    public function __construct()
    {
        $this->domain = Request::getHost();
        $this->schema = Request::getScheme();
        $this->cacheManager = new SiteCacheManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrent(): ?SiteContract
    {
        if (!$this->current) {
            $this->detectionByHost();
        }

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
     * {@inheritDoc}
     */
    public function setSite(?SiteContract $site): SiteManagerContract
    {
        $this->current = $site;
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
        $site = $this->getCurrent();
        if (!$site || !$site->is_protected) {
            return true;
        }

        return $site->is_protected && BackendAuth::check();
    }

    /**
     * {@inheritDoc}
     */
    public function forceSsl(): bool
    {
        $site = $this->getCurrent();
        return $site && $site->is_https;
    }

    /**
     * {@inheritDoc}
     */
    public function loadingConfiguration(): void
    {
        $site = $this->getCurrent();
        if (!$site) {
            return;
        }

        Config::set('app.url', $this->schema .'://'. $site->domain);
        foreach ($site->getConfiguration() as $config) {
            if (strpos($config['key'], '::') !== false) {
                $namespace = $config['key'];
            } else {
                $namespace = $site->slug .'.'. $config['key'];
            }

            Config::set($namespace, $config['value']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate(): ?string
    {
        $site = $this->getCurrent();
        if (!$site || empty($site->theme)) {
            return null;
        }

        return $site->theme;
    }

    /**
     * {@inheritDoc}
     */
    public function mainDomain(): bool
    {
        $appDomain = str_replace(['http://', 'https://'], '', config('app.url'));
        return in_array($this->domain, ['localhost', $appDomain]);
    }
}
