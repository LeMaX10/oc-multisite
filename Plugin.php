<?php declare(strict_types=1);

namespace LeMaX10\MultiSite;

use Backend\Facades\Backend;
use Illuminate\Support\Facades\Validator;
use LeMaX10\MultiSite\Classes\Middlewares\MultisiteCmsMiddleware;
use LeMaX10\MultiSite\Classes\Middlewares\MultisiteBackendMiddleware;
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
     * @var bool
     */
    public $elevated = true;

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
    public function register(): void
    {
        parent::register();

        $this->app->singleton(SiteManagerContract::class, static function (): SiteManagerContract {
            return SiteManager::instance();
        });

        require_once  __DIR__ .'/helpers.php';
    }

    /**
     *
     */
    public function boot(): void
    {
        parent::boot();

        if (!$this->app->runningInConsole()) {
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
            $controller->middleware(MultisiteCmsMiddleware::class);
        });

        \Backend\Classes\BackendController::extend(static function ($controller): void {
            $controller->middleware(MultisiteBackendMiddleware::class);
        });
    }

    /**
     * Registration Site Configuration
     */
    private function configurationCore(): void
    {
        $siteConfiguration = SiteManager::instance()->getConfiguration();
        if (!$siteConfiguration) {
            return;
        }

        $siteConfiguration->init();
    }

    /**
     * Register Domain Validation Rule
     */
    private function registerDomainValidationRule(): void
    {
        Validator::extend('domain', static function ($attribute, $value, $parameters): bool {
            return \filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
                && \preg_match('@\.(.*[A-Za-z])@', $value);
        });
    }
}
