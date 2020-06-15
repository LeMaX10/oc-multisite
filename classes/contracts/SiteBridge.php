<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes\Contracts;


use LeMaX10\MultiSite\Classes\Contracts\Entities\Site;

/**
 * Interface SiteBridge
 * @package LeMaX10\MultiSite\Classes\Contracts
 */
interface SiteBridge
{
    /**
     * @return Site|null
     */
    public function get(): ?Site;
}
