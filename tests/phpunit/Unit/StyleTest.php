<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\StyleHandler;
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
}
