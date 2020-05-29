<?php namespace LeMaX10\MultiSite\Models;

use Cms\Classes\Theme;
use Illuminate\Database\Eloquent\Builder;
use LeMaX10\MultiSite\Classes\Rules\Domain;
use LeMaX10\MultiSite\Classes\SiteCacheManager;
use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Str;
use Ramsey\Uuid\Uuid;
use LeMaX10\MultiSite\Classes\Contracts\Site as SiteContract;

/**
 * Class Site
 * @package LeMaX10\MultiSite\Models
 */
class Site extends Model implements SiteContract
{
    use Validation;

    /**
     * @var string
     */
    protected $table = 'lemax10_multisite_sites';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'domain',
        'slug',
        'alt_domains',
        'config',
        'is_active',
        'is_protected',
        'is_https'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'config'      => 'json',
        'is_active'   => 'boolean',
        'is_protected' => 'boolean',
        'is_https'     => 'boolean'
    ];

    /**
     * @var string[]
     */
    public $rules = [
        'name' => 'required|string',
        'slug' => 'required|string|unique:lemax10_multisite_sites',
        'domain' => 'required|domain|unique:lemax10_multisite_sites',
        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'is_https'  => 'boolean'
    ];

    /**
     *
     */
    public function beforeCreate()
    {
        parent::beforeCreate();

        if (!isset($this->attributes['id'])) {
            $this->attributes['id'] = Uuid::uuid4()->toString();
        }
    }

    /**
     *
     */
    public function afterSave()
    {
        parent::afterSave();
        (new SiteCacheManager)->flush();
    }

    /**
     * @return string[]
     */
    public function getThemeOptions(): array
    {
        $themes = [
            null => 'По умолчанию'
        ];

        $themeList = (new Theme)->all();
        foreach ($themeList as $theme) {
            $themes[$theme->getDirName()] = $theme->getDirName();
        }

        return $themes;
    }

    /**
     * @param Builder $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param Builder $query
     */
    public function scopeWithoutProtected(Builder $query): void
    {
        $query->where('is_protected', false);
    }

    /**
     * @param Builder $query
     * @param string $slug
     */
    public function scopeFindBySlug(Builder $query, string $slug): void
    {
        $query->where('slug', $slug);
    }

    /**
     * @param Builder $query
     * @param string $domain
     */
    public function scopeFindByDomain(Builder $query, string $domain): void
    {
        $domain = trim(Str::lower($domain));
        $query->where(static function (Builder $query) use ($domain): void {
            $query->where('domain', $domain);

            if (supportedJsonDb()) {
                $query->orWhereRaw("JSON_SEARCH('alt_domains', 'one', '{$domain}')");
            } else { //fallback
                $query->orWhere('alt_domains', 'like', '%' . $domain . '%');
            }
        });
    }

    /**
     * @param string $value
     */
    public function setAltDomainsAttribute(string $value): void
    {
        $value = array_unique(explode(' ', $value));
        $this->attributes['alt_domains'] = \json_encode($value);
    }

    /**
     * @return string
     */
    public function getAltDomainsAttribute(): string
    {
        return \implode(' ', $this->getDomains());
    }

    /**
     * @return array
     */
    public function getDomains(): array
    {
        $altDomains = \json_decode($this->getOriginal('alt_domains'), true);
        $domains = \array_merge([$this->domain], (array) $altDomains);
        return (array) $domains;
    }

    /**
     * @return array
     */
    public function getResponseHeaders(): array
    {
        $headers = $this->config['headers'] ?? [];
        return (array) $headers;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        $configuration = $this->config['config'] ?? [];
        return (array) $configuration;
    }
}
