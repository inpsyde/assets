<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\Style;

class StyleTest extends AbstractTestCase
{

    public function testBasic()
    {
        $expectedHandle = 'foo';
        $expectedUrl = 'foo.css';

        $testee = new Style($expectedHandle, $expectedUrl);

        static::assertInstanceOf(Asset::class, $testee);
        static::assertSame($expectedUrl, $testee->url());
        static::assertSame($expectedHandle, $testee->handle());
        static::assertSame('all', $testee->media());
        static::assertSame(Asset::FRONTEND, $testee->location());
        static::assertSame(StyleHandler::class, $testee->handler());
    }

    public function testMedia()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertSame('all', $testee->media());

        $testee->forMedia($expected);
        static::assertSame($expected, $testee->media());
    }

    public function testInlineStyles()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertNull($testee->inlineStyles());

        $testee->withInlineStyles($expected);
        static::assertSame([$expected], $testee->inlineStyles());
    }


    public function testUseAsyncFilter()
    {
        $testee = new Style('handle', 'foo.css');
        static::assertEmpty($testee->filters());

        $testee->useAsyncFilter();
        static::assertSame([AsyncStyleOutputFilter::class], $testee->filters());
    }
}
