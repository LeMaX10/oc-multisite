<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes\Policies;


use Backend\Models\User;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site;

/**
 * Class BackendUserPolicy
 * @package LeMaX10\MultiSite\Classes\Policies
 */
class BackendUserPolicy
{
    /**
     * Access to view site
     * @param User $user
     * @param Site $site
     * @return bool
     */
    public function view(User $user, Site $site): bool
    {
        return true;
    }
}
