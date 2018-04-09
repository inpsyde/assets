<?php # -*- coding: utf-8 -*-

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
