<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes\Observers;

use LeMaX10\MultiSite\Models\Site;
use Illuminate\Support\Facades\File;
use October\Rain\Support\Str;

/**
 * Class TemplateObserver
 * @package LeMaX10\MultiSite\Classes\Observers
 */
class TemplateObserver
{
    /**
     * @param Site $site
     */
    public function creating(Site $site): void
    {
        if ($site->getTemplate() === '-1') {
            $templateNewName = Str::camel($site->getSlug());
            File::copyDirectory(
                plugins_path('lemax10/multisite/support/stub/template'),
                themes_path($templateNewName)
            );

            $site->attributes['theme'] = $templateNewName;
        }
    }
}
