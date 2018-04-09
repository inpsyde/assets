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

    public function testLocalizeCallable()
    {
        $expected = ['foo' => 'bar'];

        $testee = new Script(
            'handle',
            'script.js',
            Asset::TYPE_SCRIPT,
            [
                'localize' => function () use ($expected) {
                    return $expected;
                },
            ]
        );

        static::assertSame($expected, $testee->localize());
    }

    public function testDataCallable()
    {
        $expected = ['foo' => 'bar'];

        $testee = new Script(
            'handle',
            'script.js',
            Asset::TYPE_SCRIPT,
            [
                'data' => function () use ($expected) {
                    return $expected;
                },
            ]
        );

        static::assertSame($expected, $testee->data());
    }

    public function testEnqueueCallable()
    {
        $expected = true;

        $testee = new Script(
            'handle',
            'script.js',
            Asset::TYPE_SCRIPT,
            [
                'enqueue' => function () use ($expected) {
                    return $expected;
                },
            ]
        );

        static::assertSame($expected, $testee->enqueue());
    }
}
