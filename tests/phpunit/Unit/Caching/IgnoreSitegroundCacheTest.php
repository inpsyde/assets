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

use Inpsyde\Assets\Caching\IgnoreSitegroundCache;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class IgnoreSitegroundCacheTest extends AbstractTestCase
{
    // phpcs:disable Squiz.PHP.Eval.Discouraged
    // phpcs:disable Inpsyde.CodeQuality.VariablesName.SnakeCaseVar
    public function testIsInstalled(): void
    {
        if (!class_exists('SiteGround_Optimizer\Loader\Loader')) {
            eval('namespace SiteGround_Optimizer\Loader { class Loader {} }');
        }

        $ignoreSitegroundCache = new IgnoreSitegroundCache();
        $this->assertTrue($ignoreSitegroundCache->isInstalled());
    }

    public function testApply(): void
    {

        $ignoreSitegroundCache = new IgnoreSitegroundCache();
        $ignoreSitegroundCache->apply([]);

        self::assertNotFalse(has_filter('sgo_js_minify_exclude', 'function (array $scripts)'));
        self::assertNotFalse(
            has_filter(
                'sgo_javascript_combine_exclude',
                'function (array $scripts)'
            )
        );
        self::assertNotFalse(has_filter('sgo_css_minify_exclude', 'function (array $styles)'));
        self::assertNotFalse(has_filter('sgo_css_combine_exclude', 'function (array $styles)'));
    }

    public function testApplyExcludedHandlers(): void
    {
        $excluded = ['excluded-1', 'excluded-2'];
        $toExclude = ['to-exclude-1', 'to-exclude-2'];

        $ignoreSitegroundCacheReflection = new \ReflectionClass(IgnoreSitegroundCache::class);
        $applyExcludedHandlesMethod = $ignoreSitegroundCacheReflection->getMethod('applyExcludedHandles');
        $applyExcludedHandlesMethod->setAccessible(true);

        static::assertSame(
            ['excluded-1', 'excluded-2', 'to-exclude-1', 'to-exclude-2'],
            $applyExcludedHandlesMethod->invoke(new IgnoreSitegroundCache(), $excluded, $toExclude)
        );
    }
}
