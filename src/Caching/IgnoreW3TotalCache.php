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
        add_filter('w3tc_minify_js_do_tag_minification', function (bool $doMinification, string $scriptTag) use ($handles) {
            assert(is_array($handles[Script::class]));
            return $this->determineMinification($doMinification, $scriptTag, $handles[Script::class]);
        }, 10, 2);

        /**
         * Ignore Styles
         */
        add_filter('w3tc_minify_css_do_tag_minification', function (bool $doMinification, string $scriptTag) use ($handles) {
            assert(is_array($handles[Style::class]));
            return $this->determineMinification($doMinification, $scriptTag, $handles[Style::class]);
        }, 10, 2);
    }

    protected function determineMinification(bool $doMinification, string $scriptTag, array $handles): bool
    {

        foreach ($handles as $handle) {
            if (strpos($scriptTag, (string)$handle) !== false) {
                return false;
            }
        }
        return $doMinification;
    }
}
