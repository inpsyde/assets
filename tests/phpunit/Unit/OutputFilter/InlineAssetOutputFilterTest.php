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
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class InlineAssetOutputFilterTest extends AbstractTestCase
{

    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new InlineAssetOutputFilter());
    }

    public function testRenderStyle()
    {

        $expectedVersion = 'foo';
        $expectedHandle = 'bar';

        $stub = \Mockery::mock(Asset::class . ',' . Style::class);
        $stub->expects('filePath')->andReturn(__DIR__ . '/../../../fixtures/style.css');
        $stub->expects('version')->andReturn($expectedVersion);
        $stub->expects('handle')->andReturn($expectedHandle);

        $input = '<link rel="stylesheet" href="https://localhost.com/style.css" />';

        $testee = new InlineAssetOutputFilter();
        $result = $testee($input, $stub);

        static::assertNotSame($input, $result);

        static::assertContains('<style', $result);
        static::assertContains('data-id="' . $expectedHandle . '"', $result);
        static::assertContains('data-version="' . $expectedVersion . '"', $result);
        static::assertContains('</style>', $result);
    }

    public function testRenderScript()
    {

        $expectedVersion = 'foo';
        $expectedHandle = 'bar';

        $stub = \Mockery::mock(Asset::class . ',' . Script::class);
        $stub->expects('filePath')->andReturn(__DIR__ . '/../../../fixtures/script.js');
        $stub->expects('version')->andReturn($expectedVersion);
        $stub->expects('handle')->andReturn($expectedHandle);

        $input = '<script src="https://localhost.com/script.js"></script>';

        $testee = new InlineAssetOutputFilter();
        $result = $testee($input, $stub);

        static::assertNotSame($input, $result);

        static::assertContains('<script', $result);
        static::assertContains('data-id="' . $expectedHandle . '"', $result);
        static::assertContains('data-version="' . $expectedVersion . '"', $result);
        static::assertContains('</script>', $result);
    }

    public function testRenderNonExistingFile()
    {
        $stub = \Mockery::mock(Asset::class . ',' . Script::class);
        $stub->expects('filePath')->andReturn('non-existing.file');

        $expected = '<link rel="stylesheet" href="https://localhost.com/style.css" />';

        $testee = new InlineAssetOutputFilter();
        $result = $testee($expected, $stub);

        static::assertSame($expected, $result);
    }
}
