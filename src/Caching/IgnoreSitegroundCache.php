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
        add_filter('sgo_js_minify_exclude', static function (array $scripts) use ($handles) {
            assert(is_array($handles[Script::class]));
            return array_merge($scripts, $handles[Script::class]);
        });

        add_filter(
            'sgo_javascript_combine_exclude',
            static function (array $scripts) use ($handles) {
                assert(is_array($handles[Script::class]));
                return array_merge($scripts, $handles[Script::class]);
            }
        );

        /**
         * Ignore Styles
         */
        add_filter('sgo_css_minify_exclude', static function (array $styles) use ($handles) {
            assert(is_array($handles[Style::class]));
            return array_merge($styles, $handles[Style::class]);
        });
        add_filter('sgo_css_combine_exclude', static function (array $styles) use ($handles) {
            assert(is_array($handles[Style::class]));
            return array_merge($styles, $handles[Style::class]);
        });
    }
}
