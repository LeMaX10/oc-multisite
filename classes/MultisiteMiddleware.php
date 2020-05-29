<?php namespace LeMaX10\MultiSite\Classes;

use Illuminate\Support\Facades\App;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager;
use LeMaX10\MultiSite\Models\Site;

/**
 * Class MultisiteMiddleware
 * @package LeMaX10\MultiSite\Classes
 */
class MultisiteMiddleware
{
    /**
     * @var SiteManager
     */
    private $siteManager;
    /**
     * @var
     */
    private $response;

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
        $this->response = $next($request);
        if (!$this->siteManager->mainDomain() && !$this->siteManager->id()) {
            return App::abort(503, 'Service Unavailable');
        }

        if (!$this->siteManager->access()) {
            return App::abort(401, 'Unauthorized.');
        }

        if ($this->siteManager->forceSsl() && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        $this->attachHeaders($this->siteManager->getCurrent());
        return $this->response;
    }

    /**
     * @param Site|null $site
     */
    private function attachHeaders(?Site $site): void
    {
        if (!$site || !$site->getResponseHeaders()) {
            return;
        }

        foreach ($site->getResponseHeaders() as $header) {
            $this->response->header($header['key'], $header['value']);
        }
    }
}
