<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Caching;

use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

/**
 * Add this tag to the script: script data-wpfc-render=“false”
 */

class IgnoreSitegroundCache implements IgnorePluginCacheInterface
{
    public function isInstalled(): bool
    {
        return class_exists('SiteGround_Optimizer\Loader\Loader');
    }

    public function apply(array $handles): void
    {
        /**
         * Ignore Javascript
         */
        add_filter('sgo_js_minify_exclude', function (array $scripts) use ($handles) {
            assert(is_array($handles[Script::class]));
            return $this->applyExcludedHandles($scripts, $handles[Script::class]);
        });

        add_filter(
            'sgo_javascript_combine_exclude',
            function (array $scripts) use ($handles) {
                assert(is_array($handles[Script::class]));
                return $this->applyExcludedHandles($scripts, $handles[Script::class]);
            }
        );

        /**
         * Ignore Styles
         */
        add_filter('sgo_css_minify_exclude', function (array $styles) use ($handles) {
            assert(is_array($handles[Style::class]));
            return $this->applyExcludedHandles($styles, $handles[Style::class]);
        });
        add_filter('sgo_css_combine_exclude', function (array $styles) use ($handles) {
            assert(is_array($handles[Style::class]));
            return $this->applyExcludedHandles($styles, $handles[Style::class]);
        });
    }

    protected function applyExcludedHandles(array $excluded, array $toExclude): array
    {

        return array_merge($excluded, $toExclude);
    }
}
