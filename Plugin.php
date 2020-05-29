<?php namespace LeMaX10\MultiSite;

use Backend\Facades\Backend;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use LeMaX10\MultiSite\Classes\Support\DatabaseSupport;
use LeMaX10\MultiSite\Classes\MultisiteMiddleware;
use System\Classes\PluginBase;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager as SiteManagerContract;
use LeMaX10\MultiSite\Classes\SiteManager;

/**
 * Class Plugin
 * @package LeMaX10\MultiSite
 */
class Plugin extends PluginBase
{

    /**
     * @var
     */
    private $siteManager;

    /**
     * @return array|string[]
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Multisite',
            'description' => 'Simple Multisite Plugin',
            'author'      => 'RDLTeam',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * @return array|array[]
     */
    public function registerNavigation()
    {
        return [
            'builder' => [
                'label'       => 'Multisite',
                'url'         => Backend::url('lemax10/multisite/sites'),
                'icon'        => 'icon-sitemap',
                'permissions' => ['*'],
                'order'       => 200,
            ]
        ];
    }
    /**
     * @return array
     */
    public function registerComponents()
    {
        return [

        ];
    }

    /**
     *
     */
    public function boot()
    {
        parent::boot();

        require_once  __DIR__ .'/helpers.php';

        $this->app->bind(DatabaseSupport::class);
        $this->app->singleton(SiteManagerContract::class, static function (): SiteManagerContract {
            return new SiteManager();
        });

        if (!$this->app->runningInConsole()) {
            $this->siteManager = app(SiteManagerContract::class);

            $this->registerMiddleware();
            $this->configurationCore();
        }

        $this->registerDomainValidationRule();
    }

    /**
     * Registration Site Middlewares
     */
    private function registerMiddleware(): void
    {
        \Cms\Classes\CmsController::extend(static function ($controller): void {
            $controller->middleware(MultisiteMiddleware::class);
        });
    }

    /**
     * Registration Site Configuration
     */
    private function configurationCore(): void
    {
        $this->siteManager->loadingConfiguration();

        $template = $this->siteManager->getTemplate();
        if ($template) {
            Event::listen('cms.theme.getActiveTheme', static function () use ($template): string {
                return $template;
            });
        }
    }

    /**
     * Register Domain Validation Rule
     */
    private function registerDomainValidationRule(): void
    {
        Validator::extend('domain', static function ($attribute, $value, $parameters) {
            return (filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
                && preg_match('@\.(.*[A-Za-z])@', $value));
        });
    }
}
