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

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Exception\InvalidArgumentException;
use Inpsyde\Assets\Exception\MissingArgumentException;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class AssetFactoryTest extends AbstractTestCase
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
    public function testBasic(): void
    {
        $expectedHandle = 'foo';
        $expectedType = Script::class;
        $expectedUrl = 'foo.js';

        $asset = AssetFactory::create(
            [
                'handle' => $expectedHandle,
                'url' => $expectedUrl,
                'type' => $expectedType,
            ]
        );

        static::assertInstanceOf(Script::class, $asset);
        static::assertInstanceOf($expectedType, $asset);
        static::assertSame($expectedUrl, $asset->url());
        static::assertSame($expectedHandle, $asset->handle());
        static::assertSame(Asset::FRONTEND, $asset->location());
    }

    /**
     * @test
     */
    public function testCreateLocation(): void
    {
        $expectedLocation = Asset::BACKEND;

        $asset = AssetFactory::create(
            [
                'handle' => 'foo',
                'location' => $expectedLocation,
                'url' => 'foo.js',
                'type' => Script::class,
            ]
        );

        static::assertSame($expectedLocation, $asset->location());
    }

    /**
     * @param mixed $input
     * @param array $expected
     *
     * @test
     *
     * @dataProvider provideDependencies
     */
    public function testDependencies($input, array $expected): void
    {
        $asset = AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.js',
                'type' => Script::class,
                'dependencies' => $input,
            ]
        );

        static::assertSame($expected, $asset->dependencies());
    }

    /**
     * @see testDependencies
     */
    public function provideDependencies(): \Generator
    {
        yield "string" => [
            'dependency-1',
            ['dependency-1'],
        ];

        yield "int" => [
            1,
            ["1"],
        ];

        yield "multiple dependencies" => [
            ['dependency-1', 'dependency-2', 'dependency-3'],
            ['dependency-1', 'dependency-2', 'dependency-3'],
        ];

        yield "non scalar - class" => [
            new \stdClass(),
            [],
        ];
    }

    /**
     * @test
     */
    public function testCreateMultipleLocations(): void
    {
        $expected = Asset::FRONTEND | Asset::BACKEND | Asset::CUSTOMIZER;
        $asset = AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => $expected,
                'type' => Script::class,
            ]
        );

        static::assertSame($expected, $asset->location());
    }

    /**
     * @test
     * @dataProvider provideInvalidConfig
     */
    public function testInvalidConfig(array $config): void
    {
        $this->expectException(MissingArgumentException::class);

        AssetFactory::create($config);
    }

    /**
     * @test
     */
    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => Asset::FRONTEND,
                'type' => \stdClass::class,
            ]
        );
    }

    /**
     * @test
     * @deprecated Loader\PhpArrayFileLoaderTest
     */
    public function testCreateFromFile(): void
    {
        $content = <<<FILE
<?php 
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

return [
    [
        'handle' => 'foo',
        'url' => 'foo.css',
        'location' => Asset::FRONTEND,
        'type' => Style::class,
    ],
    [
        'handle' => 'bar',
        'url' => 'bar.js',
        'location' => Asset::FRONTEND,
        'type' => Script::class,
    ],
];
FILE;
        $filePath = vfsStream::newFile('config.php')
            ->withContent($content)
            ->at($this->root)
            ->url();

        $assets = AssetFactory::createFromFile($filePath);
        static::assertCount(2, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
    }

    /**
     * @test
     * @deprecated Loader\PhpArrayLoaderTest
     */
    public function testCreateFromArray(): void
    {
        $input = [
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => Asset::FRONTEND,
                'type' => Style::class,
            ],
            [
                'handle' => 'bar',
                'url' => 'bar.js',
                'location' => Asset::FRONTEND,
                'type' => Script::class,
            ],
        ];

        $assets = AssetFactory::createFromArray($input);
        static::assertCount(2, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidConfig(): \Generator
    {
        yield 'missing type' => [
            [
                'handle' => 'foo',
                'url' => 'foo.css',
            ],
        ];

        yield 'missing url' => [
            [
                'handle' => 'foo',
                'location' => Asset::FRONTEND,
            ],
        ];
    }
}
