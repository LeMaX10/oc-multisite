<?php namespace LeMaX10\MultiSite\Classes\Contracts;


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
    public function forceSsl(): bool;

    /**
     * Initial configuration
     */
    public function loadingConfiguration(): void;

    /**
     * @return string|null
     */
    public function getTemplate(): ?string;

    /**
     * @return bool
     */
    public function mainDomain(): bool;
}
