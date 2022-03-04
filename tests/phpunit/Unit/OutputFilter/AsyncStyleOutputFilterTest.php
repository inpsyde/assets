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

use Brain\Monkey;
use Inpsyde\Assets\Asset;
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

        $stub = \Mockery::mock(Asset::class);
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
