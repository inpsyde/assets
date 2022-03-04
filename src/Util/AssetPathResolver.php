<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets\Util;

class AssetPathResolver
{
    /**
     * Attempt to resolve an assets path based on its URL.
     *
     * @param string $url
     * @return null|string
     */
    public static function resolve(string $url): ?string
    {
        $normalizedUrl = set_url_scheme($url);

        return self::resolveForThemeUrl($normalizedUrl)
            ?? self::resolveForPluginUrl($normalizedUrl)
            ?? self::resolveForVendorUrl($normalizedUrl)
            ?? null;
    }

    /**
     * @param string $normalizedUrl
     * @return string|null
     * @psalm-suppress PossiblyFalseArgument
     */
    public static function resolveForVendorUrl(string $normalizedUrl): ?string
    {
        // Now let's see if it's inside vendor.
        // This is problematic, this is why vendor assets should be "published".

        $fullVendorPath = wp_normalize_path(realpath(__DIR__ . '/../../../'));
        $abspath = wp_normalize_path(ABSPATH);
        $abspathParent = dirname($abspath);

        $relativeVendorPath = null;
        if (strpos($fullVendorPath, $abspath) === 0) {
            $relativeVendorPath = substr($normalizedUrl, strlen($abspath));
        } elseif (strpos($fullVendorPath, $abspathParent) === 0) {
            $relativeVendorPath = substr($normalizedUrl, strlen($abspathParent));
        }

        if (!$relativeVendorPath) {
            // vendor is not inside ABSPATH, nor inside its parent
            return null;
        }

        $relativeVendorPath = trim($relativeVendorPath, '/');

        // problematic, as said above: we are assuming vendor URL, but this assumption isn't safe
        $vendorUrl = network_site_url("/{$relativeVendorPath}");

        if (strpos($normalizedUrl, $vendorUrl) === 0) {
            $relative = trim((string) substr($normalizedUrl, strlen($vendorUrl)), '/');

            return trailingslashit($fullVendorPath) . $relative;
        }

        return null;
    }

    /**
     * @param string $normalizedUrl
     * @return string|null
     */
    public static function resolveForThemeUrl(string $normalizedUrl): ?string
    {
        $themeUrl = get_template_directory_uri();
        $childUrl = get_stylesheet_directory_uri();

        $base = '';
        $relativeThemeUrl = null;
        if (strpos($normalizedUrl, $childUrl) === 0) {
            $base = get_stylesheet_directory();
            $relativeThemeUrl = substr($normalizedUrl, strlen($childUrl));
        } elseif (strpos($normalizedUrl, $themeUrl) === 0) {
            $base = get_template_directory();
            $relativeThemeUrl = substr($normalizedUrl, strlen($themeUrl));
        }

        return $relativeThemeUrl
            ? trailingslashit($base) . trim($relativeThemeUrl, '/')
            : null;
    }

    /**
     * @param string $normalizedUrl
     * @return string|null
     */
    public static function resolveForPluginUrl(string $normalizedUrl): ?string
    {
        $pluginsUrl = plugins_url('');
        $muPluginsUrl = plugins_url('', WPMU_PLUGIN_DIR . '/file.php');

        $basePath = '';
        $relativePluginUrl = null;
        if (strpos($normalizedUrl, $pluginsUrl) === 0) {
            $basePath = WP_PLUGIN_DIR;
            $relativePluginUrl = substr($normalizedUrl, strlen($pluginsUrl));
        } elseif (strpos($normalizedUrl, $muPluginsUrl) === 0) {
            $basePath = WPMU_PLUGIN_DIR;
            $relativePluginUrl = substr($normalizedUrl, strlen($muPluginsUrl));
        }

        return $relativePluginUrl
            ? trailingslashit(wp_normalize_path($basePath)) . trim($relativePluginUrl, '/')
            : null;
    }
}
