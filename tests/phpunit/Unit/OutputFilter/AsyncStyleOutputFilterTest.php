<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\OutputFilter;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class AsyncStyleOutputFilterTest extends AbstractTestCase
{

    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new AsyncStyleOutputFilter());
    }

    public function testRender()
    {
        $testee = new AsyncStyleOutputFilter();

        $expectedUrl = 'foo.jpg';
        $input = '<link rel="stylesheet" url="'.$expectedUrl.'" />';

        Monkey\Functions\when('esc_url')->justReturn($expectedUrl);
        Monkey\Functions\when('esc_attr')->justReturn($expectedUrl);

        $stub = \Mockery::mock(Asset::class);
        $stub->expects('url')->once()->andReturn($expectedUrl);
        $stub->expects('version')->once()->andReturn('');

        $output = $testee($input, $stub);

        static::assertContains('<link rel="preload" href="'.$expectedUrl.'" as="style"', $output);
        // is input wrapped into <noscript>-Tag?
        static::assertContains("<noscript>{$input}</noscript>", $output);
        // polyfill
        static::assertContains('<script>', $output);
        static::assertContains('</script>', $output);
    }
}
