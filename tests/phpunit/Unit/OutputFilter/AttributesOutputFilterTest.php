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
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

use function Brain\Monkey\Functions\expect;

class AttributesOutputFilterTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic()
    {
        $testee = new AttributesOutputFilter();

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn([]);

        $input = '<script src="foo.js"></script>';

        static::assertInstanceOf(AssetOutputFilter::class, $testee);
        static::assertSame($input, $testee($input, $stub));
    }

    /**
     * @param array $attributes
     * @param array $expected
     * @param array $notExpected
     *
     * @test
     * @dataProvider provideAttributes
     */
    public function testRenderWithAttributes(array $attributes, array $expected, array $notExpected)
    {
        // esc_attr always returns a string, even if we input an integer.
        expect('esc_attr')->andReturnUsing(
            static function ($input): string {
                return (string) $input;
            }
        );

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn($attributes);

        $input = '<script src="script.js"></script>';

        $testee = new AttributesOutputFilter();
        $output = $testee($input, $stub);

        foreach ($expected as $test) {
            static::assertStringContainsString($test, $output);
        }
        foreach ($notExpected as $test) {
            static::assertStringNotContainsString($test, $output);
        }
    }

    /**
     * @return \Generator
     */
    public function provideAttributes(): \Generator
    {
        yield 'string value' => [
            [
                'key' => 'value',
            ],
            ['key="value"'],
            [],
        ];

        yield 'integer value' => [
            [
                'key' => 1,
            ],
            ['key="1"'],
            [],
        ];

        yield 'bool true value' => [
            [
                'key' => true,
            ],
            ['key="key"'],
            [],
        ];

        yield 'bool false value' => [
            [
                'key' => false,
            ],
            [],
            ['key="key"'],
        ];

        yield 'overwriting src-attribute' => [
            [
                'key' => 'value',
                'src' => 'not-allowed.js',
            ],
            ['key="value"'],
            ['src="not-allowed.js"'],
        ];
    }

    /**
     * @test
     */
    public function testRenderNotOverwriteExistingAttributes()
    {
        $expectedKey = 'src';
        $expectedValue = 'foo.js';
        $expectedAttribute = sprintf('%s="%s"', $expectedKey, $expectedValue);

        expect('esc_attr')->andReturnFirstArg();

        $stub = \Mockery::mock(Asset::class);
        // We're trying to overwrite the "src" with "bar.js".
        $stub->expects('attributes')->andReturn([$expectedKey => 'bar.js']);

        $input = sprintf('<script %s></script>', $expectedAttribute);

        $testee = new AttributesOutputFilter();
        static::assertStringContainsString($expectedAttribute, $testee($input, $stub));
    }

    /**
     * @test
     */
    public function testRenderInlineScriptsNotChanged()
    {
        $expectedKey = 'key';
        $expectedValue = 'value';
        $expectedAttributes = [$expectedKey => $expectedValue];

        $expectedBefore = "<script>var before = 'bar';</script>";
        $expectedAfter = "<script>var after = 'bar';</script>";

        expect('esc_attr')->andReturnFirstArg();

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn($expectedAttributes);

        $input = $expectedBefore . '<script src="foo.js"></script>' . $expectedAfter;

        $testee = new AttributesOutputFilter();
        $output = $testee($input, $stub);
        static::assertStringContainsString($expectedBefore, $output);
        static::assertStringContainsString($expectedAfter, $output);
    }

    /**
     * @param string $expectedBefore
     * @param string $expectedAfter
     *
     * @test
     * @dataProvider provideRenderWithInlineScripts
     */
    public function testRenderWithInlineScripts(string $expectedBefore, string $expectedAfter)
    {
        expect('esc_attr')->andReturnFirstArg();
        $stub = \Mockery::mock(Asset::class);
        $stub->expects('attributes')->andReturn(['foo' => 'bar']);

        $input = $expectedBefore . '<script src="foo.js"></script>' . $expectedAfter;

        $testee = new AttributesOutputFilter();
        $output = $testee($input, $stub);
        static::assertStringContainsString($expectedBefore, $output);
        static::assertStringContainsString($expectedAfter, $output);
    }

    public function provideRenderWithInlineScripts(): \Generator
    {
        $singleLineJs = '(function(){ console.log("script with single line"); })();';
        $multiLineJs = <<<JS
(function() {
    console.log("script with multiple lines")
})();
JS;

        yield 'before single line' => [
            $singleLineJs,
            '',
        ];

        yield 'after single line' => [
            '',
            $singleLineJs,
        ];

        yield 'before and after single line' => [
            $singleLineJs,
            $singleLineJs,
        ];

        yield 'before multi, after single line' => [
            $multiLineJs,
            $singleLineJs,
        ];

        yield 'before and after multi line' => [
            $multiLineJs,
            $multiLineJs,
        ];
    }
}
