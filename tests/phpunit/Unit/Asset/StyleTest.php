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

namespace Inpsyde\Assets\Tests\Unit\Asset;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class StyleTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic()
    {
        $testee = new Style('foo', 'foo.css');

        static::assertInstanceOf(Asset::class, $testee);
        static::assertSame('all', $testee->media());
        static::assertSame(Asset::FRONTEND | Asset::ACTIVATE, $testee->location());
        static::assertSame(StyleHandler::class, $testee->handler());
    }

    /**
     * @test
     */
    public function testMedia()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertSame('all', $testee->media());

        $testee->forMedia($expected);
        static::assertSame($expected, $testee->media());
    }

    /**
     * @test
     */
    public function testInlineStyles()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertNull($testee->inlineStyles());

        $testee->withInlineStyles($expected);
        static::assertSame([$expected], $testee->inlineStyles());
    }

    /**
     * @test
     */
    public function testUseAsyncFilter()
    {
        $testee = new Style('handle', 'foo.css');
        static::assertEmpty($testee->filters());

        $testee->useAsyncFilter();
        static::assertSame([AsyncStyleOutputFilter::class], $testee->filters());
    }

    /**
     * @param string $element
     * @param array $cssVars
     * @param array $expected
     *
     * @dataProvider provideCssVars
     */
    public function testWithCssVars(string $element, array $cssVars, array $expected)
    {
        $testee = new Style('handle', 'foo.css');
        $testee->withCssVars($element, $cssVars);

        static::assertSame($expected, $testee->cssVars());
    }

    public function provideCssVars(): \Generator
    {
        yield 'non-prefixed vars' => [
            '.some-element',
            ['white' => '#fff', 'black' => '#000'],
            ['.some-element' => ['--white' => '#fff', '--black' => '#000']],
        ];

        yield 'prefixed vars' => [
            ':root',
            ['--white' => '#fff', '--black' => '#000'],
            [':root' => ['--white' => '#fff', '--black' => '#000']],
        ];

        yield 'prefixed and non-prefixed vars' => [
            'div',
            ['white' => '#fff', '--black' => '#000'],
            ['div' => ['--white' => '#fff', '--black' => '#000']],
        ];
    }

    /**
     * @test
     */
    public function testCssVarsAsString()
    {
        $element = ':root';
        $vars = ['white' => '#fff', 'black' => '#000'];

        $expected = ":root{--white:#fff;--black:#000;}";

        $testee = new Style('handle', 'foo.css');
        $testee->withCssVars($element, $vars);

        static::assertSame($expected, $testee->cssVarsAsString());
    }

    /**
     * @test
     */
    public function testMultipleCssVarsAsString()
    {
        $element1 = ':root';
        $vars1 = ['white' => '#fff', 'black' => '#000'];

        $element2 = 'div';
        $vars2 = ['--grey' => '#ddd'];

        $expected = ":root{--white:#fff;--black:#000;}div{--grey:#ddd;}";

        $testee = new Style('handle', 'foo.css');
        $testee->withCssVars($element1, $vars1);
        $testee->withCssVars($element2, $vars2);

        static::assertSame($expected, $testee->cssVarsAsString());
    }
}
