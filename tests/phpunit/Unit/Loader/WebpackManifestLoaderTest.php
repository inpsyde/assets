<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\WebpackManifestLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class WebpackManifestLoaderTest extends AbstractTestCase
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
     * @param string $json
     * @param string $expectedHandle
     * @param string $expectedFileName
     * @param string $expectedClass
     *
     * @dataProvider provideManifest
     *
     * @throws \Throwable
     */
    public function testLoadFromManifest(
        string $json,
        string $expectedHandle,
        string $expectedFileName,
        string $expectedClass
    ) {
        $expectedDirUrl = 'http://localhost.com/assets/';
        $expectedFileUrl = $expectedDirUrl.$expectedFileName;

        $testee = new WebpackManifestLoader();
        $testee->withDirectoryUrl($expectedDirUrl);
        $assets = $testee->load($this->mockManifestJson($json));

        static::assertCount(1, $assets);

        /** @var Asset $asset */
        $asset = $assets[0];
        static::assertSame($expectedHandle, $asset->handle());
        static::assertSame($expectedFileUrl, $asset->url());
        static::assertInstanceOf($expectedClass, $asset);
    }

    public function provideManifest()
    {
        yield 'style asset' => [
            '{"my-handle": "style.css"}',
            'my-handle',
            'style.css',
            Style::class,
        ];

        yield 'script asset' => [
            '{"my-handle": "script.js"}',
            'my-handle',
            'script.js',
            Script::class,
        ];

        yield 'with file path in handle-key' => [
            '{"./script.js": "script.js"}',
            'script',
            'script.js',
            Script::class,
        ];

        yield 'absolute file path' => [
            '{"my-handle": "/script.js"}',
            'my-handle',
            'script.js',
            Script::class,
        ];

        yield 'current direction file path' => [
            '{"my-handle": "./script.js"}',
            'my-handle',
            'script.js',
            Script::class,
        ];

        yield 'asset in sub folder' => [
            '{"my-handle": "./sub-folder/script.js"}',
            'my-handle',
            'sub-folder/script.js',
            Script::class,
        ];
    }

    public function testLoadFromManifestMultipleAssets()
    {
        $json = json_encode(
            [
                'script' => 'script.js',
                'style' => 'style.css',
            ]
        );

        $testee = new WebpackManifestLoader();
        $assets = $testee->load($this->mockManifestJson($json));

        static::assertCount(2, $assets);

        static::assertInstanceOf(Script::class, $assets[0]);
        static::assertInstanceOf(Style::class, $assets[1]);
    }

    public function testLoadFromManifestNotSupportedTypes()
    {
        $json = json_encode(
            [
                'an image' => 'cat.jpeg',
                'a font' => 'fancy-font.woff',
            ]
        );

        $testee = new WebpackManifestLoader();
        $assets = $testee->load($this->mockManifestJson($json));

        static::assertCount(0, $assets);
    }

    /**
     * @dataProvider provideManifestWithAlternativeUrl
     *
     * @param string $json
     * @param string $alternativeUrl
     * @param string $expectedUrl
     *
     * @throws \Throwable
     */
    public function testLoadFromManifestWithAlternativeUrl(string $json, string $alternativeUrl, string $expectedUrl)
    {
        $testee = new WebpackManifestLoader();
        $testee->withDirectoryUrl($alternativeUrl);
        $assets = $testee->load($this->mockManifestJson($json));

        /** @var Asset $asset */
        $asset = $assets[0];
        static::assertSame($expectedUrl, $asset->url());
    }

    public function provideManifestWithAlternativeUrl()
    {
        $url = 'http://localhost.com/';

        yield 'default' => [
            '{"my-handle": "style.css"}',
            $url,
            $url.'style.css',
        ];

        yield 'Asset in sub-folder absolute' => [
            '{"my-handle": "/path/to/sub-folder/style.css"}',
            $url,
            $url.'path/to/sub-folder/style.css',
        ];

        yield 'Asset in sub-folder to current' => [
            '{"my-handle": "./path/to/sub-folder/style.css"}',
            $url,
            $url.'path/to/sub-folder/style.css',
        ];

        yield 'Asset with URL' => [
            '{"my-handle": "https://foo.bar/style.css"}',
            $url,
            $url.'style.css',
        ];

        yield 'Asset with URL and sub-folder' => [
            '{"my-handle": "https://foo.bar/baz/style.css"}',
            $url,
            $url.'baz/style.css',
        ];
    }

    private function mockManifestJson(string $json): string
    {
        return vfsStream::newFile('manifest.json')
            ->withContent($json)
            ->at($this->root)
            ->url();
    }
}