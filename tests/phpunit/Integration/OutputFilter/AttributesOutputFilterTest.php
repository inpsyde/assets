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

namespace Inpsyde\Assets\Tests\Integration\OutputFilter;

use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\Script;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class AttributesOutputFilterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!class_exists(\WP_HTML_Tag_Processor::class)) {
            require ABSPATH . 'wp-includes/html-api/class-wp-html-attribute-token.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-span.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-text-replacement.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-tag-processor.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-unsupported-exception.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-active-formatting-elements.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-open-elements.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-token.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-processor-state.php';
            require ABSPATH . 'wp-includes/html-api/class-wp-html-processor.php';
        }

        if (!function_exists('esc_attr')) {
            eval('function esc_attr(string $attribute): string
                  {
                      return $attribute;
                  }');
        }
    }

    public function testBasic(): void
    {
        $testee = new AttributesOutputFilter();

        $stub = new Script('stub-script', 'https://syde.com/foo.js');

        $input = '<script src="foo.js"></script>';

        static::assertInstanceOf(AssetOutputFilter::class, $testee);
        static::assertSame($input, $testee($input, $stub));
    }

    /**
     * @dataProvider provideAttributes
     */
    public function testRenderWithAttributes(array $attributes, array $expected, array $notExpected): void
    {
        $stub = new Script('stub-script', 'https://syde.com/foo.js');
        $stub->withAttributes($attributes);

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

    public function testRenderNotOverwriteExistingAttributes(): void
    {
        $expectedKey = 'src';
        $expectedValue = 'foo.js';
        $expectedAttribute = sprintf('%s="%s"', $expectedKey, $expectedValue);

        $stub = new Script('stub-script', 'https://syde.com/foo.js');
        // We're trying to overwrite the "src" with "bar.js".
        $stub->withAttributes([$expectedKey => 'bar.js']);

        $input = sprintf('<script %s></script>', $expectedAttribute);

        $testee = new AttributesOutputFilter();
        static::assertStringContainsString($expectedAttribute, $testee($input, $stub));
    }

    public function testRenderInlineScriptsNotChanged()
    {
        $expectedKey = 'key';
        $expectedValue = 'value';
        $expectedAttributes = [$expectedKey => $expectedValue];

        $expectedBefore = "<script>var before = 'bar';</script>";
        $expectedAfter = "<script>var after = 'bar';</script>";

        $stub = new Script('stub-script', 'https://syde.com/foo.js');
        $stub->withAttributes($expectedAttributes);

        $input = $expectedBefore . '<script src="foo.js"></script>' . $expectedAfter;

        $testee = new AttributesOutputFilter();
        $output = $testee($input, $stub);
        static::assertStringContainsString($expectedBefore, $output);
        static::assertStringContainsString($expectedAfter, $output);
    }

    /**
     * @dataProvider provideRenderWithInlineScripts
     */
    public function testRenderWithInlineScripts(string $expectedBefore, string $expectedAfter): void
    {
        $stub = new Script('stub-script', 'https://syde.com/foo.js');
        $stub->withAttributes(['foo' => 'bar']);

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
        $multiByteLine = '<script>(function(){ console.log("Lösungen ї 𠀋"); })();</script>';
        $nonStandardUrl = '<script src="http://[::1]:5173/path/to/build/@vite/client"></script>';

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

        yield 'before and after multibyte line' => [
            $multiByteLine,
            $multiByteLine,
        ];

        yield 'before and after URL with non-alphanumeric characters' => [
            $nonStandardUrl,
            $nonStandardUrl,
        ];
    }
}
