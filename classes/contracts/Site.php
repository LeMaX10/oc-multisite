<?php


namespace LeMaX10\MultiSite\Classes\Contracts;


/**
 * Interface Site
 * @package LeMaX10\MultiSite\Classes\Contracts
 */
interface Site
{
    /**
     * @return array
     */
    public function getResponseHeaders(): array;

    /**
     * @return array
     */
    public function getConfiguration(): array;
}
