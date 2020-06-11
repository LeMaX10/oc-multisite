<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Models;

use Cms\Classes\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use LeMaX10\MultiSite\Classes\Rules\Domain;
use LeMaX10\MultiSite\Classes\SiteCacheManager;
use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Str;
use Ramsey\Uuid\Uuid;
use LeMaX10\MultiSite\Classes\Contracts\Entities\Site as SiteEntityContract;

/**
 * Class Site
 * @package LeMaX10\MultiSite\Models
 */
class Site extends Model implements SiteEntityContract
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
        'theme',
        'is_active',
        'is_protected',
        'is_https'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'is_active'   => 'boolean',
        'is_protected' => 'boolean',
        'is_https'     => 'boolean'
    ];

    /**
     * @var string[]
     */
    protected $jsonable = [
        'alt_domains',
        'config'
    ];

    /**
     * @var string[]
     */
    public $rules = [
        'name' => 'required|string',
        'slug' => 'required|string|unique:lemax10_multisite_sites',

        'alt_domains'  => 'sometimes|array',
        'alt_domains.*' => 'sometimes|domain',
        'domain' => 'required|domain|unique:lemax10_multisite_sites',

        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'is_https'  => 'boolean'
    ];

    /**
     *
     */
    public function beforeValidate(): void
    {
        if (!empty($this->alt_domains)) {
            $altDomains = trim($this->attributes['alt_domains'], '"');
            $altDomains = explode(' ', $altDomains);
            $this->alt_domains = array_unique($altDomains);
        } else {
            $this->alt_domains = [];
        }
    }

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
    public function getThemeOptions($value, $formData): array
    {
        $themes = [
            null => 'По умолчанию',

        ];

        if (!$this->exists) {
            $themes[-1] = 'Создать автоматический';
        }

        /** @var Theme $theme */
        foreach (Theme::all() as $theme) {
            $themeName = '('. $theme->getDirName() .') '. $theme->getConfigValue('name');
            $themes[$theme->getDirName()] = $themeName;
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
            $query->whereDomain($domain);

            if (supportedJsonDb()) {
                $query->orWhereRaw("JSON_SEARCH(alt_domains, 'one', ?) is not null", [$domain]);
            } else { //fallback
                $query->orWhere('alt_domains', 'like', "%{$domain}%");
            }
        });
    }

    /**
     * @return string
     */
    public function getAltDomainsFieldAttribute(): string
    {
        return implode(' ', (array) $this->alt_domains);
    }

    /**
     * @return array
     */
    public function getDomains(): array
    {
        $domains = \array_merge([$this->domain], (array) $this->alt_domains);
        return (array) $domains;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseHeaders(): array
    {
        return (array) Arr::get($this->config, 'headers');
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(): array
    {
        return (array) Arr::get($this->config, 'config');
    }

    /**
     * {@inheritDoc}
     */
    public function getCmsConfiguration(): array
    {
        $configuration = $this->config['cms'] ?? [];
        return (array) $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * {@inheritDoc}
     */
    public function forceHttps(): bool
    {
        return $this->is_https;
    }

    /**
     * {@inheritDoc}
     */
    public function isProtected(): bool
    {
        return $this->is_protected;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate(): ?string
    {
        return $this->theme;
    }

    /**
     * {@inheritDoc}
     */
    public function disableBackend(): bool
    {
        return !Arr::get($this->config, 'security.is_backend', false);
    }

    /**
     * {@inheritDoc}
     */
    public function getRobotsContent(): string
    {
        $isRobots = (bool) Arr::get($this->config, 'is_robots');
        if (!$isRobots) {
            return '';
        }

        return Arr::get($this->config, 'robots', '');
    }

    /**
     * @return bool
     */
    public function isPagesCache(): bool
    {
        return (bool) Arr::get($this->config, 'is_page_cache');
    }

    /**
     * @return bool
     */
    public function safeMode(): bool
    {
        return (bool) Arr::get($this->config, 'security.safeMode');
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        // TODO: Implement getDomain() method.
    }
}
