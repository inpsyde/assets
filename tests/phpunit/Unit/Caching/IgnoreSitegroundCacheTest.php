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

use function PHPUnit\Framework\assertSame;

class IgnoreSitegroundCacheTest extends AbstractTestCase
{
    public function testApplyExcludedHandlers(): void
    {
        $excluded = ['excluded-1', 'excluded-2'];
        $toExclude = ['to-exclude-1', 'to-exclude-2'];

        $ignoreSitegroundCacheReflection = new \ReflectionClass(IgnoreSitegroundCache::class);
        $applyExcludedHandlesMethod = $ignoreSitegroundCacheReflection->getMethod('applyExcludedHandles');
        $applyExcludedHandlesMethod->setAccessible(true);

        assertSame(
            ['excluded-1', 'excluded-2', 'to-exclude-1', 'to-exclude-2'],
            $applyExcludedHandlesMethod->invoke(new IgnoreSitegroundCache(), $excluded, $toExclude)
        );
    }
}
