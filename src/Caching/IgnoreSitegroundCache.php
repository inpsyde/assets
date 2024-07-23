<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        add_filter('sgo_js_minify_exclude', function (array $scripts) use($handles) {
            return array_merge($scripts, $handles[Script::class]);
        });

        add_filter('sgo_javascript_combine_exclude', function (array $scripts) use($handles){
            return array_merge($scripts, $handles[Script::class]);
        });

        /**
         * Ignore Styles
         */
        add_filter('sgo_css_minify_exclude', function (array $styles) use($handles){
            return array_merge($styles, $handles[Style::class]);
        });
        add_filter('sgo_css_combine_exclude', function (array $styles) use($handles){
            return array_merge($styles, $handles[Style::class]);
        });
    }
}