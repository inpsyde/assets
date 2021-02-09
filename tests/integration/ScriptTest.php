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

namespace Inpsyde\Assets\Tests\Integration;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\ArrayLoader;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use WP_UnitTestCase;

class ScriptTest extends WP_UnitTestCase
{
    /**
     * Sets up the environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDeferFilterWithPrepend(): void
    {
        $script = $this->registerAssets([[
            'handle'   => 'foo',
            'url'      => 'https://example.org/foo.js',
            'location' => Asset::FRONTEND,
            'type'     => Script::class,
            'version'  => '1.0',
            'filters'  => [
                DeferScriptOutputFilter::class,
            ],
            'inline'   => [
                'before' => [
                    'var foo = "bar";',
                ]
            ],
        ]]);

        static::assertSame("<script type='text/javascript' id='foo-js-before'>\nvar foo = \"bar\";\n</script>\n<script defer type='text/javascript' src='https://example.org/foo.js?ver=1.0' id='foo-js'></script>\n", $script);
    }

    /**
     * Register and enqueue assets
     *
     * @param array $assets
     * @return string
     */
    private function registerAssets(array $assets): string
    {
        global $wp_scripts;
        $wp_scripts = new \WP_Scripts();

        $loader = new ArrayLoader();
        $assets = $loader->load($assets);

        add_action(
            AssetManager::ACTION_SETUP,
            function (AssetManager $assetManager) use ($assets) {
                foreach ($assets as $asset) {
                    $assetManager->register($asset);
                }
            }
        );
        do_action('wp_enqueue_scripts');

        ob_start();
        wp_print_scripts();
        return ob_get_clean();
    }
}
