<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Brain\Monkey;
use Inpsyde\Assets\FilterAwareAsset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AsyncStyleOutputFilterTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new AsyncStyleOutputFilter());
    }

    /**
     * @test
     */
    public function testRender()
    {
        $testee = new AsyncStyleOutputFilter();

        $expectedUrl = 'foo.jpg';
        $input = '<link rel="stylesheet" url="' . $expectedUrl . '" />';

        Monkey\Functions\when('esc_url')->justReturn($expectedUrl);
        Monkey\Functions\when('esc_attr')->justReturn($expectedUrl);

        $stub = \Mockery::mock(FilterAwareAsset::class);
        $stub->expects('url')->once()->andReturn($expectedUrl);
        $stub->expects('version')->once()->andReturn('');

        $output = $testee($input, $stub);

        static::assertStringContainsString(
            '<link rel="preload" href="' . $expectedUrl . '" as="style"',
            $output
        );
        // is input wrapped into <noscript>-Tag?
        static::assertStringContainsString("<noscript>{$input}</noscript>", $output);
        // polyfill
        static::assertStringContainsString('<script>', $output);
        static::assertStringContainsString('</script>', $output);
    }
}
