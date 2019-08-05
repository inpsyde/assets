<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\AbstractWebpackLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class AbstractWebpackLoaderTest extends AbstractTestCase
{

    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\FileNotFoundException
     */
    public function testLoadJsonDataFileNotFound()
    {
        $testee = new class extends AbstractWebpackLoader
        {

            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath): array
            {
                return parent::load($filePath);
            }
        };

        $testee->load('undefined-file');
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\InvalidResourceException
     */
    public function testLoadJsonParseException()
    {
        $resource = vfsStream::newFile('malformed.json')
            ->withContent('{"foo" "bar"}')
            ->at($this->root)
            ->url();

        $testee = new class extends AbstractWebpackLoader
        {

            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function load($filePath): array
            {
                return parent::load($filePath);
            }
        };

        $testee->load($resource);
    }

    public function testResolveClassByExtension()
    {
        $testee = new class extends AbstractWebpackLoader
        {

            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function resolveClassByExtension(string $extension): string
            {
                return parent::resolveClassByExtension($extension);
            }
        };

        static::assertSame(Script::class, $testee->resolveClassByExtension('js'));
        static::assertSame(Style::class, $testee->resolveClassByExtension('css'));
    }

    public function testResolveDependencies()
    {
        $expectedDependencies = ['foo', 'bar', 'baz'];

        vfsStream::newFile('script.deps.json')
            ->withContent(json_encode($expectedDependencies))
            ->at($this->root);

        $testee = new class extends AbstractWebpackLoader
        {

            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function resolveDependencies(string $filePath): array
            {
                return parent::resolveDependencies($filePath);
            }
        };
        $file = vfsStream::newFile('script.js')
            ->at($this->root)
            ->url();

        $dependencies = $testee->resolveDependencies($file);

        static::assertSame($expectedDependencies, $dependencies);
    }

    /**
     * @dataProvider provideAssetLocations
     *
     * @param string $inputFile
     * @param int $expectedLocation
     *
     * @throws \Throwable
     */
    public function testResolveLocations(string $inputFile, int $expectedLocation)
    {
        $testee = new class extends AbstractWebpackLoader
        {

            protected function parseData(array $data, string $resource): array
            {
                return [];
            }

            public function resolveLocation(string $fileName): int
            {
                return parent::resolveLocation($fileName);
            }
        };

        static::assertSame($expectedLocation, $testee->resolveLocation($inputFile));
    }

    public function provideAssetLocations()
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
