<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site;
use October\Rain\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

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
        $this->configureMedia();
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

        $cmsConfigs = (array) Arr::get($this->site->config, 'cms', []);
        foreach ($cmsConfigs as $key => $value) {
            Config::set('cms.'. $key, $value);
        }

        Config::set('cms.disableCoreUpdates', true);
        Config::set('cms.linkPolicy', $this->site->forceHttps() ? 'secure' : 'detected');
        Config::set('cms.backendUri', 'backend');
    }

    /**
     *
     */
    public function configureEnviroinment(): void
    {
        $configuration = Arr::except($this->site->getConfiguration(), [
            'cms.disableCoreUpdates',
            'cms.backendUri'
        ]);

        foreach ($configuration as $config) {
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

        Route::get('robots.txt', config('lemax10.multisite::config.controllers.robots'));
    }

    /**
     *
     */
    public function configureMedia(): void
    {
        $isIndividual = (bool) Arr::get($this->site->config, 'is_media');
        if (!$isIndividual) {
            return;
        }

        Config::set('cms.storage.media.folder', $this->site->getSlug());
        Config::set('cms.storage.media.path', '/storage/app/'. $this->site->getSlug());
    }
}
