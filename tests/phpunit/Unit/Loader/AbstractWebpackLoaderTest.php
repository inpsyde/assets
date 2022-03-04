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

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Exception\FileNotFoundException;
use Inpsyde\Assets\Exception\InvalidResourceException;
use Inpsyde\Assets\Loader\AbstractWebpackLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class AbstractWebpackLoaderTest extends AbstractTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    /**
     * @test
     */
    public function testLoadJsonDataFileNotFound(): void
    {
        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath): array
            {
                return parent::load($filePath);
            }
        };

        $this->expectException(FileNotFoundException::class);

        $loader->load('undefined-file');
    }

    /**
     * @test
     */
    public function testLoadJsonParseException(): void
    {
        $resource = vfsStream::newFile('malformed.json')
            ->withContent('{"foo" "bar"}')
            ->at($this->root)
            ->url();

        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath): array
            {
                return parent::load($filePath);
            }
        };

        $this->expectException(InvalidResourceException::class);

        $loader->load($resource);
    }

    /**
     * @test
     * @dataProvider provideAssetLocations
     */
    public function testResolveLocations(string $inputFile, int $expectedLocation): void
    {
        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function resolveLocation(string $fileName): int
            {
                return parent::resolveLocation($fileName);
            }
        };

        static::assertSame($expectedLocation, $loader->resolveLocation($inputFile));
    }

    /**
     * @return \Generator
     */
    public function provideAssetLocations(): \Generator
    {
        yield 'frontend Asset' => [
            './style.css',
            Asset::FRONTEND,
        ];

        yield 'backend Asset' => [
            'style-backend.css',
            Asset::BACKEND,
        ];

        yield 'login Asset' => [
            'style-login.css',
            Asset::LOGIN,
        ];

        yield 'customizer Asset' => [
            'style-customizer.css',
            Asset::CUSTOMIZER,
        ];

        yield 'Gutenberg Block Asset' => [
            'style-block.css',
            Asset::BLOCK_EDITOR_ASSETS,
        ];
    }
}
