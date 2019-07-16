<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\Script;

class ScriptTest extends AbstractTestCase
{

    public function testBasic()
    {
        $testee = new Script('foo', 'foo.js');

        static::assertInstanceOf(Asset::class, $testee);
        static::assertTrue($testee->inFooter());
        static::assertEmpty($testee->localize());
        static::assertSame(ScriptHandler::class, $testee->handler());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }

    public function testWithTranslation()
    {
        $testee = new Script('handle', 'script.js');

        static::assertEmpty($testee->translation());

        $expectedDomain = 'foo';
        $expectedPath = '/path/to/some/file.json';
        $expected = ['domain' => $expectedDomain, 'path' => $expectedPath];

        $testee->withTranslation($expectedDomain, $expectedPath);
        static::assertSame($expected, $testee->translation());
    }

    public function testWithLocalize()
    {
        $expectedKey = 'foo';
        $expectedValue = ['bar' => 'baz'];
        $expected = [$expectedKey => $expectedValue];
        $testee = new Script('handle', 'script.js');

        static::assertEmpty($testee->localize());

        $testee->withLocalize($expectedKey, $expectedValue);

        static::assertSame($expected, $testee->localize());
    }

    public function testInFooter()
    {
        $testee = new Script('handle', 'script.js');

        // default is true
        static::assertTrue($testee->inFooter());

        $testee->isInHeader();
        static::assertFalse($testee->inFooter());

        $testee->isInFooter();
        static::assertTrue($testee->inFooter());
    }

    public function testLocalizeCallable()
    {
        $expectedKey = 'foo';
        $expectedValue = ['bar' => 'baz'];
        $expected = [$expectedKey => $expectedValue];

        $testee = new Script(
            'handle',
            'script.js',
            Asset::FRONTEND,
            [
                'localize' => [
                    $expectedKey => function () use ($expectedValue) {
                        return $expectedValue;;
                    },
                ],
            ]
        );

        static::assertSame($expected, $testee->localize());
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

    public function testInlineScripts()
    {
        $testee = new Script('handle', 'foo.js');

        $expectedAppended = 'foo';
        $expectedPrepended = 'foo';

        static::assertEmpty($testee->inlineScripts());

        $testee->appendInlineScript($expectedAppended);
        $testee->prependInlineScript($expectedPrepended);

        static::assertEquals(
            ['before' => [$expectedAppended], 'after' => [$expectedPrepended]],
            $testee->inlineScripts()
        );
    }

    public function testUseAsyncFilter()
    {
        $testee = new Script('handle', 'foo.js');
        static::assertEmpty($testee->filters());

        $testee->useAsyncFilter();
        static::assertSame([AsyncScriptOutputFilter::class], $testee->filters());
    }

    public function testUseDeferFilter()
    {
        $testee = new Script('handle', 'foo.js');
        static::assertEmpty($testee->filters());

        $testee->useDeferFilter();
        static::assertSame([DeferScriptOutputFilter::class], $testee->filters());
    }
}
