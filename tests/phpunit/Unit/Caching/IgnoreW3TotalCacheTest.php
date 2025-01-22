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

use Inpsyde\Assets\Caching\IgnoreW3TotalCache;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class IgnoreW3TotalCacheTest extends AbstractTestCase
{
    // phpcs:disable Squiz.PHP.Eval.Discouraged
    // phpcs:disable Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
    public function testIsInstalled(): void
    {
        if (!class_exists('W3TC\Root_Loader')) {
            eval('namespace W3TC { class Root_Loader {} }');
        }

        $IgnoreW3TotalCache = new IgnoreW3TotalCache();
        $this->assertTrue($IgnoreW3TotalCache->isInstalled());
    }

    public function testApply(): void
    {

        $ignoreSitegroundCache = new IgnoreW3TotalCache();
        $ignoreSitegroundCache->apply([]);

        self::assertNotFalse(
            has_filter(
                'w3tc_minify_js_do_tag_minification',
                'function (bool $doMinification, string $scriptTag)'
            )
        );
        self::assertNotFalse(
            has_filter(
                'w3tc_minify_css_do_tag_minification',
                'function (bool $doMinification, string $scriptTag)'
            )
        );
    }

    public function testDetermineMinification(): void
    {
        $scriptTag = '<script src="example.js" id="assets-plugin-script-js"></script>';

        $ignoreW3TotalCacheReflection = new \ReflectionClass(IgnoreW3TotalCache::class);
        $determineMinificationMethod = $ignoreW3TotalCacheReflection->getMethod('determineMinification');
        $determineMinificationMethod->setAccessible(true);

        static::assertSame(
            false,
            $determineMinificationMethod->invoke(new IgnoreW3TotalCache(), true, $scriptTag, ['assets-plugin-script'])
        );
        static::assertSame(
            true,
            $determineMinificationMethod->invoke(new IgnoreW3TotalCache(), true, $scriptTag, ['assets-plugin-script-example'])
        );
        static::assertSame(
            true,
            $determineMinificationMethod->invoke(new IgnoreW3TotalCache(), true, $scriptTag, [])
        );
        static::assertSame(
            false,
            $determineMinificationMethod->invoke(new IgnoreW3TotalCache(), false, $scriptTag, [])
        );
    }
}
