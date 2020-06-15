<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes\Middlewares;

use Illuminate\Support\Facades\App;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager;
use LeMaX10\MultiSite\Models\Site;

/**
 * Class MultisiteCmsMiddleware
 * @package LeMaX10\MultiSite\Classes
 */
class MultisiteBackendMiddleware
{
    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * MultisiteMiddleware constructor.
     * @param SiteManager $siteManager
     */
    public function __construct(SiteManager $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $response = $next($request);
        $currentSite = $this->siteManager->getCurrent();

        if (!$this->siteManager->isMain() && !$currentSite) {
            return App::abort(503, 'Service Unavailable');
        }

        if ($currentSite) {
            if ($currentSite->disableBackend()) {
                return App::abort(404, 'Page not found');
            }

            if ($currentSite->forceHttps() && !$request->isSecure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }

            $this->siteManager->getConfiguration()->configureResponseHeaders($response);
        }

        return $response;
    }
}
