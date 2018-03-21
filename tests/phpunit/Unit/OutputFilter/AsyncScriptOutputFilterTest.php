<?php # -*- coding: utf-8 -*-

namespace Inpyde\Assets\Tests\Unit\OutputFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use Inpyde\Assets\OutputFilter\AssetOutputFilter;
use Inpyde\Assets\OutputFilter\AsyncScriptOutputFilter;

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
