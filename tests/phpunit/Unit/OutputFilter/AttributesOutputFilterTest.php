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

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AttributesOutputFilterTest extends AbstractTestCase
{
    public function testIfTagProcessorIsUnavailable(): void
    {
        $this->expectDeprecation();
        $testee = new AttributesOutputFilter();

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn([
            'key' => 'value',
        ]);

        $input = '<script src="foo.js"></script>';

        static::assertInstanceOf(AssetOutputFilter::class, $testee);
        static::assertSame($input, $testee($input, $stub));
    }
}
