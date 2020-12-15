<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$libraryPath = dirname(__DIR__, 2);
$vendorPath = "{$libraryPath}/vendor";
if (!realpath($vendorPath)) {
    die('Please install via Composer before running tests.');
}

putenv('LIBRARY_PATH=' . $libraryPath);

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', "{$vendorPath}/autoload.php");
}

defined('ABSPATH') or define('ABSPATH', "{$vendorPath}/wordpress/wordpress/");

require_once "{$vendorPath}/antecedent/patchwork/Patchwork.php";
require_once "{$vendorPath}/autoload.php";

unset($libraryPath, $vendorPath);
