<?php


namespace LeMaX10\MultiSite\Classes;

use Illuminate\Support\Facades\Cache;

/**
 * Class SiteCacheManager
 * @package LeMaX10\MultiSite\Classes
 */
class SiteCacheManager
{
    /**
     * @var string
     */
    protected $tagMemoryStorage = 'multisite-sites';

    /**
     * @param string $value
     * @return string
     */
    public function generateId(string $value): string
    {
        return md5(trim($value));
    }

    /**
     * @param string $domain
     * @return string|null
     */
    public function getByDomain(string $domain): ?string
    {
        $tagId = $this->generateId($domain);
        return $this->get($tagId);
    }

    /**
     * @param string $tagId
     * @return string|null
     */
    public function get(string $tagId): ?string
    {
        return Cache::get($tagId);
    }

    /**
     * @param string $domain
     * @param string $siteId
     */
    public function put(string $domain, string $siteId): void
    {
        $tagId = $this->generateId($domain);
        Cache::forever($tagId, $siteId);

        $this->saveTags($tagId);
    }

    /**
     * @param string $tagId
     */
    protected function saveTags(string $tagId): void
    {
        $inMemory = Cache::get($this->tagMemoryStorage, []);
        array_push($inMemory, $tagId);

        Cache::forever($this->tagMemoryStorage, $inMemory);
    }

    /**
     *
     */
    public function flush(): void
    {
        $inMemory = Cache::get($this->tagMemoryStorage, []);
        foreach ($inMemory as $tagId) {
            Cache::forget($tagId);
        }
    }
}
