<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Exception\FileNotFoundException;
use Inpsyde\Assets\Exception\InvalidResourceException;
use Inpsyde\Assets\Loader\AbstractWebpackLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\ScriptModule;
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
        \Brain\Monkey\Functions\expect('esc_html')->andReturnFirstArg();

        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath, array $entrypoints = []): array
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
        \Brain\Monkey\Functions\expect('esc_html')->andReturnFirstArg();

        $resource = vfsStream::newFile('malformed.json')
            ->withContent('{"foo" "bar"}')
            ->at($this->root)
            ->url();

        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath, array $entrypoints = []): array
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

    public function testBuildModulesAssets(): void
    {
        vfsStream::newFile('assets/my.mjs')
            ->withContent('console.log("Hello, World!");')
            ->at($this->root);

        $handle = 'asset-handle';
        $fileUrl = 'https://example.com/assets/my.mjs';
        $filePath = vfsStream::url('tmp/assets/my.module.mjs');

        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function buildAsset(string $handle, string $fileUrl, string $filePath): ?Asset
            {
                return parent::buildAsset($handle, $fileUrl, $filePath);
            }
        };

        $asset = $loader->buildAsset($handle, $fileUrl, $filePath);

        $this->assertInstanceOf(ScriptModule::class, $asset);
        $this->assertSame($handle, $asset->handle());
        $this->assertSame($fileUrl, $asset->url());
    }

    public function testBuildCustomModulesAssets(): void
    {
        vfsStream::newFile('assets/my.module.js')
            ->withContent('console.log("Hello, World!");')
            ->at($this->root);

        $handle = 'asset-handle';
        $fileUrl = 'https://example.com/assets/my.module.js';
        $filePath = vfsStream::url('tmp/assets/my.module.js');

        $loader = new class extends AbstractWebpackLoader {
            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function buildAsset(string $handle, string $fileUrl, string $filePath): ?Asset
            {
                return parent::buildAsset($handle, $fileUrl, $filePath);
            }
        };

        $asset = $loader->buildAsset($handle, $fileUrl, $filePath);

        $this->assertInstanceOf(ScriptModule::class, $asset);
        $this->assertSame($handle, $asset->handle());
        $this->assertSame($fileUrl, $asset->url());
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
