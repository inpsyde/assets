<?php

/**
 * Plugin Name: Assets Plugin
 * Plugin URI: https://syde.com
 * Description: A basic example plugin using inpsyde/assets
 * Version: 1.0
 * Author: Syde GmbH
 * Author URI:
 * License: MIT
 */

require __DIR__ . '/vendor/autoload.php';

use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

add_action(
    AssetManager::ACTION_SETUP,
    function(AssetManager $assetManager) {
        $testScript = new Script('assets-plugin-script', plugin_dir_url(__FILE__) . 'resources/test-script.js');
        $testScript2 = new Script('assets-plugin-script-2', plugin_dir_url(__FILE__) . 'resources/test-script-2.js');

        $testScript->isInHeader();
        $testScript2->isInHeader();

        $assetManager->register(
            $testScript,
            $testScript2,
            new Style('assets-plugin-style', plugin_dir_url(__FILE__) . 'resources/test-style.css'),
            new Style('assets-plugin-style-2', plugin_dir_url(__FILE__) . 'resources/test-style-2.css')
        );

        $assetManager->ignoreCache();
    }
);