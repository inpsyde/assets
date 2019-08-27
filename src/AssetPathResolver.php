<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

class AssetPathResolver
{

    /**
     * Resolving a given Asset URL to a filePath.
     *
     * @param string $url
     *
     * @return null|string
     *
     * // phpcs:disable
     */
    public static function resolve(string $url): ?string
    {
        $normalizedUrl = set_url_scheme($url);

        // First let's see if it's a theme or child theme URL
        $filePath = self::resolveForThemeUrl($normalizedUrl);
        if ($filePath !== null) {
            return $filePath;
        }

        // Now let's see if it's a plugin ot a MU plugin
        $filePath = self::resolveForPluginUrl($normalizedUrl);
        if ($filePath !== null) {
            return $filePath;
        }

        $filePath = self::resolveForVendorUrl($normalizedUrl);

        return $filePath;
    }

    public static function resolveForVendorUrl(string $normalizedUrl): ?string
    {
        // Now let's see if it's inside vendor.
        // This is problematic, this is why vendor assets should be "published".

        $fullVendorPath = wp_normalize_path(realpath(__DIR__.'/../../../'));
        $abspath = wp_normalize_path(ABSPATH);
        $abspathParent = dirname($abspath);

        $relativeVendorPath = null;
        if (strpos($fullVendorPath, $abspath) === 0) {
                $relativeVendorPath = substr($normalizedUrl, strlen($abspath));
        } elseif (strpos($fullVendorPath, $abspathParent) === 0) {
            $relativeVendorPath = substr($normalizedUrl, strlen($abspathParent));
        }

        if (! $relativeVendorPath) {
            // vendor is not inside ABSPATH, nor inside its parent
            return null;
        }

        $relativeVendorPath = trim($relativeVendorPath, '/');

        // problematic, as said above: we are assuming vendor URL, but this assumption isn't safe
        $vendorUrl = network_site_url("/{$relativeVendorPath}");

        if (strpos($normalizedUrl, $vendorUrl) === 0) {
            $relative = trim(substr($normalizedUrl, strlen($vendorUrl)), '/');

            return trailingslashit($fullVendorPath).$relative;
        }

        return null;
    }

    public static function resolveForThemeUrl(string $normalizedUrl): ?string
    {
        $themeUrl = get_template_directory_uri();
        $childUrl = get_stylesheet_directory_uri();

        $relativeThemeUrl = null;
        $isChild = strpos($normalizedUrl, $childUrl) === 0;
        if ($isChild) {
            $relativeThemeUrl = trim(substr($normalizedUrl, strlen($childUrl)), '/');
        } elseif (strpos($normalizedUrl, $themeUrl) === 0) {
            $relativeThemeUrl = trim(substr($normalizedUrl, strlen($themeUrl)), '/');
        }

        if (! $relativeThemeUrl) {
            return null;
        }

        $base = $isChild
            ? get_stylesheet_directory()
            : get_template_directory();

        return trailingslashit($base).$relativeThemeUrl;
    }

    public static function resolveForPluginUrl(string $normalizedUrl): ?string
    {
        $pluginsUrl = plugins_url('');
        $muPluginsUrl = plugins_url('', WPMU_PLUGIN_DIR.'/file.php');

        $relativePluginUrl = null;
        $isMu = strpos($normalizedUrl, $muPluginsUrl) === 0;
        if (strpos($normalizedUrl, $pluginsUrl) === 0) {
            $relativePluginUrl = trim(substr($normalizedUrl, strlen($pluginsUrl)), '/');
        } elseif ($isMu) {
            $relativePluginUrl = trim(substr($normalizedUrl, strlen($muPluginsUrl)), '/');
        }

        if (! $relativePluginUrl) {
            return null;
        }

        $basePath = rtrim(
            wp_normalize_path(
                $isMu
                    ? WPMU_PLUGIN_DIR
                    : WP_PLUGIN_DIR
            ),
            '/'
        );

        return trailingslashit($basePath).$relativePluginUrl;
    }
}
