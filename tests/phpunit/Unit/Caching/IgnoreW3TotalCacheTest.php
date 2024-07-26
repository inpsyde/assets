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

use function PHPUnit\Framework\assertSame;

class IgnoreW3TotalCacheTest extends AbstractTestCase
{
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
