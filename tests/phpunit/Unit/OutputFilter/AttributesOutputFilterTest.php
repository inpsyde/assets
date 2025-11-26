<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Brain\Monkey;
use Inpsyde\Assets\FilterAwareAsset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AttributesOutputFilterTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new AttributesOutputFilter());
    }

    /**
     * @test
     */
    public function testRender()
    {
        $testee = new AttributesOutputFilter();

        $expectedUrl = 'foo.js';
        $input = '<script src="' . $expectedUrl . '"></script>';

        Monkey\Functions\when('esc_attr')->returnArg(1);
        Monkey\Functions\when('wp_kses_uri_attributes')->justReturn(['href', 'src', 'action']);

        $stub = \Mockery::mock(FilterAwareAsset::class);
        $stub->expects('attributes')->once()->andReturn([
            'type' => 'module',
        ]);

        $output = $testee($input, $stub);

        static::assertStringContainsString('type="module"', $output);
        static::assertStringContainsString('src="' . $expectedUrl . '"', $output);
    }

    /**
     * @test
     */
    public function testAttributesAppliedWithPrependedInlineScript()
    {
        $testee = new AttributesOutputFilter();

        $expectedUrl = 'foo.js';
        // Simulates WordPress output when prependInlineScript is used:
        // inline script appears BEFORE the main script tag
        $input = '<script>const foo = "bar";</script>'
            . '<script src="' . $expectedUrl . '"></script>';

        Monkey\Functions\when('esc_attr')->returnArg(1);
        Monkey\Functions\when('wp_kses_uri_attributes')->justReturn(['href', 'src', 'action']);

        $stub = \Mockery::mock(FilterAwareAsset::class);
        $stub->expects('attributes')->once()->andReturn([
            'defer' => true,
        ]);

        $output = $testee($input, $stub);

        // The defer attribute should be applied to the script with src
        static::assertStringContainsString('defer="defer"', $output);
        static::assertStringContainsString('src="' . $expectedUrl . '"', $output);
    }
}
