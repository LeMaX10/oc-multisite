<?php namespace LeMaX10\MultiSite\Classes;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site;
use \LeMaX10\MultiSite\Classes\Contracts\SiteManager;
use October\Rain\Support\Facades\Config;

/**
 * Class SiteConfiguration
 * @package LeMaX10\MultiSite\Classes
 */
class SiteConfiguration
{
    /**
     * @var Site
     */
    protected $site;

    /**
     * SiteConfiguration constructor.
     * @param Site $site
     * @param SiteManager $siteManager
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     *
     */
    public function init(): void
    {
        $this->standartConfigurationEnviroinment();
        $this->configureEnviroinment();
        $this->configureTemplate();
        $this->registerRobotsRoute();
    }

    /**
     * @param $response
     */
    public function configureResponseHeaders($response): void
    {
        foreach ($this->site->getResponseHeaders() as $header) {
            $response->header($header['key'], $header['value']);
        }
    }

    /**
     *
     */
    public function standartConfigurationEnviroinment(): void
    {
        $schema = request()->getScheme();
        if ($this->site->forceHttps()) {
            $schema = 'https';
        }

        Config::set('app.url', $schema.'://'. $this->site->domain);
        Config::set('cache.prefix', 'multisite.'. $this->site->getSlug());
        Config::set('cms.linkPolicy', $this->site->forceHttps() ? 'secure' : 'detected');
        Config::set('cms.enableSafeMode', $this->site->safeMode());
        Config::set('cms.enableRoutesCache', $this->site->isPagesCache());
    }

    /**
     *
     */
    public function configureEnviroinment(): void
    {
        foreach ($this->site->getConfiguration() as $config) {
            if (strpos($config['key'], '::') !== false) {
                $namespace = $config['key'];
            } else {
                $namespace = $this->site->getSlug() .'.'. $config['key'];
            }

            Config::set($namespace, $config['value']);
        }
    }

    /**
     *
     */
    public function configureTemplate(): void
    {
        $template = $this->site->getTemplate();
        if (!$template) {
            return;
        }

        Event::listen('cms.theme.getActiveTheme', static function () use ($template): string {
            return $template;
        });
    }

    /**
     *
     */
    public function registerRobotsRoute(): void
    {
        $robotsContent = $this->site->getRobotsContent();
        if (empty($robotsContent)) {
            return;
        }

        Route::get('robots.txt', static function () use ($robotsContent) {
            return response($robotsContent, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8'
            ]);
        });
    }
}
