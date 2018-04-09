<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class DeferScriptOutputFilterTest extends AbstractTestCase
{

    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new DeferScriptOutputFilter());
    }

    public function testRender()
    {
        $testee = new DeferScriptOutputFilter();

        $stub = \Mockery::mock(Asset::class);

        $input = '<script src="foo.js"></script>';
        $expected = '<script defer src="foo.js"></script>';

        static::assertSame($expected, $testee($input, $stub));
    }
}
