<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes\Contracts;

use LeMaX10\MultiSite\Classes\Contracts\Entities\Site;

interface SiteManager
{

    /**
     * @return string|null
     */
    public function id(): ?string;

    /**
     * @return Site|null
     */
    public function getCurrent(): ?Site;

    /**
     * @param null|Site $site
     * @return SiteManager
     */
    public function setSite(?Site $site): SiteManager;

    /**
     * @param string $id
     * @return SiteManager
     */
    public function setSiteById(string $id): SiteManager;

    /**
     *
     */
    public function detectionByHost(): void;

    /**
     * @return bool
     */
    public function access(): bool;

    /**
     * @return bool
     */
    public function mainDomain(): bool;
}
