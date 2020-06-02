<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes\Contracts\Entities;

/**
 * Interface Site
 * @package LeMaX10\MultiSite\Classes\Contracts\Entities
 */
interface Site
{
    /**
     * Get unique slug site
     * @return string
     */
    public function getSlug(): string;

    /**
     * Get all headers variables site
     * @return array
     */
    public function getResponseHeaders(): array;

    /**
     * Get all configuration variables site
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * Force redirect http to https
     * @return bool
     */
    public function forceHttps(): bool;

    /**
     * Only administrator access
     * @return bool
     */
    public function isProtected(): bool;

    /**
     * Get current template site
     * @return string|null
     */
    public function getTemplate(): ?string;

    /**
     * Disable backend in site
     * @return bool
     */
    public function disableBackend(): bool;

    /**
     * Get Robots txt content
     * @return string
     */
    public function getRobotsContent(): string;
}
