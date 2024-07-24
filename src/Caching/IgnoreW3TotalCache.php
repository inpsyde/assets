<?php

declare(strict_types=1);

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

    // phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
    public function apply(array $handles): void
    {
        /**
         * Ignore Javascript
         */
        add_filter('w3tc_minify_js_do_tag_minification', static function (bool $doMinification, string $scriptTag) use ($handles) {
            foreach ($handles[Script::class] as $handle) {
                if (strpos($scriptTag, $handle) !== false) {
                    return false;
                }
            }
            return $doMinification;
        }, 10, 2);

        /**
         * Ignore Styles
         */
        add_filter('w3tc_minify_css_do_tag_minification', static function (bool $doMinification, string $scriptTag) use ($handles) {
            foreach ($handles[Style::class] as $handle) {
                if (strpos($scriptTag, $handle) !== false) {
                    return false;
                }
            }
            return $doMinification;
        }, 10, 2);
    }
}
