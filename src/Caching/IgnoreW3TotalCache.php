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
 * Check filters
 */

class IgnoreW3TotalCache implements IgnorePluginCacheInterface
{
    public function isInstalled(): bool
    {
        return class_exists('W3TC\Root_Loader');
    }

    public function apply(array $handles): void
    {
        /**
         * Ignore Javascript
         */
        add_filter('w3tc_minify_js_do_tag_minification', function(bool $doMinification, string $scriptTag) use($handles){
            foreach($handles[Script::class] as $handle){
                if(strpos( $scriptTag, $handle ) !== false){
                    return false;
                }
            }
            return $doMinification;
        }, 10, 2);

        /**
         * Ignore Styles
         */
        add_filter('w3tc_minify_css_do_tag_minification', function(bool $doMinification, string $scriptTag) use($handles){
            foreach($handles[Style::class] as $handle){
                if(strpos( $scriptTag, $handle ) !== false){
                    return false;
                }
            }
            return $doMinification;
        }, 10, 2);
    }
}