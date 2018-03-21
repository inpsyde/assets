<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;

class ScriptTest extends AbstractTestCase
{

    public function testBasic()
    {

        $expectedHandle = 'foo';
        $expectedUrl = 'foo.js';

        $testee = new Script($expectedHandle, $expectedUrl);

        static::assertInstanceOf(Asset::class, $testee);
        static::assertSame($expectedUrl, $testee->url());
        static::assertSame($expectedHandle, $testee->handle());
        static::assertTrue($testee->inFooter());
        static::assertEmpty($testee->localize());
        static::assertSame(Asset::TYPE_SCRIPT, $testee->type());
    }
}
