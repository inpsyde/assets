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
use org\bovigo\vfs\vfsStream;

class InlineAssetOutputFilterTest extends AbstractTestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    public function testBasic()
    {
        static::assertInstanceOf(AssetOutputFilter::class, new InlineAssetOutputFilter());
    }

    public function testRenderStyle()
    {

        $expectedVersion = 'foo';
        $expectedHandle = 'bar';

        $fileStub = vfsStream::newFile('style.css')
            ->withContent('body { background: white; }')
            ->at($this->root);

        $stub = \Mockery::mock(Asset::class . ',' . Style::class);
        $stub->expects('filePath')->andReturn($fileStub->url());
        $stub->expects('version')->andReturn($expectedVersion);
        $stub->expects('handle')->andReturn($expectedHandle);

        $input = '<link rel="stylesheet" href="https://localhost.com/style.css" />';

        $testee = new InlineAssetOutputFilter();
        $result = $testee($input, $stub);

        static::assertNotSame($input, $result);

        static::assertStringContainsString('<style', $result);
        static::assertStringContainsString('data-id="' . $expectedHandle . '"', $result);
        static::assertStringContainsString('data-version="' . $expectedVersion . '"', $result);
        static::assertStringContainsString('</style>', $result);
    }

    public function testRenderScript()
    {

        $expectedVersion = 'foo';
        $expectedHandle = 'bar';

        $fileStub = vfsStream::newFile('script.js')
            ->withContent('console.log("foo");')
            ->at($this->root);

        $stub = \Mockery::mock(Asset::class . ',' . Script::class);
        $stub->expects('filePath')->andReturn($fileStub->url());
        $stub->expects('version')->andReturn($expectedVersion);
        $stub->expects('handle')->andReturn($expectedHandle);

        $input = '<script src="https://localhost.com/script.js"></script>';

        $testee = new InlineAssetOutputFilter();
        $result = $testee($input, $stub);

        static::assertNotSame($input, $result);

        static::assertStringContainsString('<script', $result);
        static::assertStringContainsString('data-id="' . $expectedHandle . '"', $result);
        static::assertStringContainsString('data-version="' . $expectedVersion . '"', $result);
        static::assertStringContainsString('</script>', $result);
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
