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

namespace Inpsyde\Assets\Tests\Unit\Caching;

use Brain\Monkey\Functions;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Caching\IgnoreCacheHandler;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use Inpsyde\Assets\Util\AssetHookResolver;
use Inpsyde\WpContext;

use function PHPUnit\Framework\assertSame;

class IgnoreCacheHandlerTest extends AbstractTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('wp_scripts')->justReturn(\Mockery::mock('WP_Scripts'));
        Functions\when('wp_styles')->justReturn(\Mockery::mock('WP_Styles'));
    }

    public function testExtractHandles(): void
    {
        $assetsManager = $this->factoryAssetManager();
        $ignoreCacheHandler = new IgnoreCacheHandler();
        $assetsManager->register(
            new Script('example-1', 'script1.js'),
            new Script('example-2', 'script2.js'),
            new Style('example-3', 'style1.css'),
            new Style('example-4', 'style2.css')
        );

        $ignoreCacheHandlerReflection = new \ReflectionClass(IgnoreCacheHandler::class);
        $extractHandlesMethod = $ignoreCacheHandlerReflection->getMethod('extractHandles');
        $extractHandlesMethod->setAccessible(true);

        static::assertSame(
            [
                Script::class => [
                    'example-1', 'example-2',
                ],
                Style::class => [
                    'example-3', 'example-4',
                ],
            ],
            $extractHandlesMethod->invoke($ignoreCacheHandler, $assetsManager)
        );
    }

    private function factoryAssetManager(?string $context = null): AssetManager
    {
        $wpContext = WpContext::new()->force($context ?? WpContext::FRONTOFFICE);
        $resolver = new AssetHookResolver($wpContext);
        return new AssetManager($resolver);
    }
}
