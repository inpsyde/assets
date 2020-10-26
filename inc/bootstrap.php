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

namespace Inpsyde\Assets;

// Exit early in case multiple Composer autoloaders try to include this file.
if (defined(__NAMESPACE__.'\BOOTSTRAPPED')) {
    return;
}

const BOOTSTRAPPED = true;

function bootstrap(): bool
{
    // Prevent function is called more than once with same path as argument (which would mean load same file again)
    static $done;
    if ($done) {
        return false;
    }
    $done = true;

    (new AssetManager())->setup();

    return $done;
}

/*
 * This file is loaded by Composer autoload, and that may happen before `add_action` is available.
 * In that case, we first try to load `plugin.php` before calling `add_action`.
 */

$addActionExists = function_exists('add_action');
if (
    $addActionExists
    || (defined('ABSPATH') && defined('WP_INC') && file_exists(ABSPATH . WP_INC . '/plugin.php'))
) {
    if (!$addActionExists) {
        require_once ABSPATH . WP_INC . '/plugin.php';
    }

    unset($addActionExists);
    add_action('wp_loaded', __NAMESPACE__ . '\\bootstrap', 99);

    return;
}

unset($addActionExists);

/**
 * If here, this file is loaded very early, probably too-much early, even before ABSPATH was defined
 * so only option we have is to "manually" write in global `$wp_filter` array.
 */

global $wp_filter;
is_array($wp_filter) or $wp_filter = [];
isset($wp_filter['wp_loaded']) or $wp_filter['wp_loaded'] = [];
isset($wp_filter['wp_loaded'][99]) or $wp_filter['wp_loaded'][99] = [];
$wp_filter['wp_loaded'][99][__NAMESPACE__ . '\\bootstrap'] = [
    'function' => __NAMESPACE__ . '\\bootstrap',
    'accepted_args' => 0,
];
