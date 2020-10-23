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
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AsyncScriptOutputFilterTest extends AbstractTestCase
{

    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new AsyncScriptOutputFilter());
    }

    public function testRender()
    {
        $testee = new AsyncScriptOutputFilter();

        $stub = \Mockery::mock(Asset::class);

        $input = '<script src="foo.js"></script>';
        $expected = '<script async src="foo.js"></script>';

        static::assertSame($expected, $testee($input, $stub));
    }
}
