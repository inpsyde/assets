<?php # -*- coding: utf-8 -*-

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
 * @example before: my-script.js | after: my-script.min.js
 *
 * @param string $file
 *
 * @return string
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
