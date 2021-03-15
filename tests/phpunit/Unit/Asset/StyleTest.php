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

    public function testBasic()
    {
        $testee = new Style('foo', 'foo.css');

        static::assertInstanceOf(Asset::class, $testee);
        static::assertSame('all', $testee->media());
        static::assertSame(Asset::FRONTEND, $testee->location());
        static::assertSame(StyleHandler::class, $testee->handler());
    }

    public function testMedia()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertSame('all', $testee->media());

        $testee->forMedia($expected);
        static::assertSame($expected, $testee->media());
    }

    public function testInlineStyles()
    {
        $expected = 'bar';

        $testee = new Style('foo', 'foo.css');

        static::assertNull($testee->inlineStyles());

        $testee->withInlineStyles($expected);
        static::assertSame([$expected], $testee->inlineStyles());
    }

    public function testUseAsyncFilter()
    {
        $testee = new Style('handle', 'foo.css');
        static::assertEmpty($testee->filters());

        $testee->useAsyncFilter();
        static::assertSame([AsyncStyleOutputFilter::class], $testee->filters());
    }
}
