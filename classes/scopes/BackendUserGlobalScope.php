<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use LeMaX10\MultiSite\Classes\Contracts\SiteManager;

/**
 * Class BackendUserGlobalScope
 * @package LeMaX10\MultiSite\Classes\Scopes
 */
final class BackendUserGlobalScope implements Scope
{
    /**
     * @var SiteManager
     */
    private $siteManager;

    /**
     * BackendUserGlobalScope constructor.
     * @param SiteManager $siteManager
     */
    public function __construct(SiteManager $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        $currentSite = $this->siteManager->getCurrent();
        if ($currentSite) {
            
        }
    }
}
