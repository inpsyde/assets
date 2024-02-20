<?php

$testsDir = str_replace('\\', '/', __DIR__);
$libDir = dirname($testsDir, 2);
$vendorDir = "{$libDir}/vendor";
$autoload = "{$vendorDir}/autoload.php";

if (!is_file($autoload)) {
    die('Please install via Composer before running tests.');
}

putenv('TESTS_DIR=' . $testsDir);
putenv('LIB_DIR=' . $libDir);
putenv('VENDOR_DIR=' . $vendorDir);

error_reporting(E_ALL); // phpcs:ignore

require_once "{$libDir}/vendor/antecedent/patchwork/Patchwork.php";

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', $autoload);
    require_once $autoload;
}



defined('ABSPATH') or define('ABSPATH', "{$vendorDir}/johnpbloch/wordpress-core/");
require_once "{$vendorDir}/johnpbloch/wordpress-core/wp-includes/class-wp-error.php";

unset($testsDir, $libDir, $vendorDir, $autoload);
