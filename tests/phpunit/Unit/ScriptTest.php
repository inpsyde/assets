<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\ScriptHandler;
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
        static::assertSame(ScriptHandler::class, $testee->handler());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }

    public function testLocalizeCallable()
    {
        $expected = ['foo' => 'bar'];

        $testee = new Script(
            'handle',
            'script.js',
            Asset::FRONTEND,
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
            Asset::FRONTEND,
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
            Asset::FRONTEND,
            [
                'enqueue' => function () use ($expected) {
                    return $expected;
                },
            ]
        );

        static::assertSame($expected, $testee->enqueue());
    }

    public function testHandler()
    {
        $expected = 'foo';

        static::assertSame(
            $expected,
            (new Script(
                'handle',
                'foo.js',
                Asset::FRONTEND,
                ['handler' => $expected]
            ))->handler()
        );
    }
}
