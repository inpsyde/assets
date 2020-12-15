<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

// Exit early in case multiple Composer autoloaders try to include this file.
if (function_exists(__NAMESPACE__.'\\assetSuffix')) {
    return;
}

/**
 * Returns ".min" if SCRIPT_DEBUG is false.
 *
 * @return string
 */
function assetSuffix(): string
{
    return defined('SCRIPT_DEBUG') && SCRIPT_DEBUG
        ? ''
        : '.min';
}

/**
 * Adding the assetSuffix() before file extension to the given file.
 *
 * @param string $file
 *
 * @return string
 * @example before: my-script.js | after: my-script.min.js
 *
 */
function withAssetSuffix(string $file): string
{
    $suffix = assetSuffix();
    $extension = '.'.pathinfo($file, PATHINFO_EXTENSION);

    return str_replace(
        $extension,
        $suffix.$extension,
        $file
    );
}

/**
 * Symlinks a folder inside the web-root for Assets, which are outside of the web-root
 * and returns a link to that folder.
 *
 * @param string $originDir
 * @param string $name
 *
 * @return string|null
 */
function symlinkedAssetFolder(string $originDir, string $name): ?string
{
    // we're using realpath here, otherwise the comparisment with
    // readlink will not work.
    $originDir = realpath($originDir);

    $folderName = '/~inpsyde-assets/';
    $rootPath = WP_CONTENT_DIR.$folderName;
    $rootUrl = WP_CONTENT_URL.$folderName;
    if (! is_dir($rootPath) && ! wp_mkdir_p($rootPath)) {
        return null;
    }

    $targetDir = $rootPath.$name;
    $targetUrl = trailingslashit($rootUrl.$name);

    if (is_link($targetDir)) {
        if (readlink($targetDir) === $originDir) {
            return $targetUrl;
        }
        unlink($targetDir);
    }

    if (! symlink($originDir, $targetDir)) {
        return null;
    }

    return $targetUrl;
}
