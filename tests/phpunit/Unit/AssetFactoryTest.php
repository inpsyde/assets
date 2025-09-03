<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Exception\InvalidArgumentException;
use Inpsyde\Assets\Exception\MissingArgumentException;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\ScriptModule;
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
    public function testInlineScripts(): void
    {
        $expectedInlineScripts = [
            'after' => [
                'var after = "bar"',
            ],
            'before' => [
                'var before = "foo"',
            ],
        ];

        $asset = AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.js',
                'type' => Script::class,
                'inline' => $expectedInlineScripts,
            ]
        );

        $inlineScripts = $asset->inlineScripts();
        static::assertArrayHasKey('after', $inlineScripts);
        static::assertSame($expectedInlineScripts['after'], $inlineScripts['after']);

        static::assertArrayHasKey('before', $inlineScripts);
        static::assertSame($expectedInlineScripts['before'], $inlineScripts['before']);
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
    public function testInvalidConfig(array $config, string $expectedExceptionType): void
    {
        \Brain\Monkey\Functions\when('esc_html')->returnArg();

        $this->expectException($expectedExceptionType);

        AssetFactory::create($config);
    }

    /**
     * @test
     */
    public function testInvalidType(): void
    {
        \Brain\Monkey\Functions\when('esc_html')->returnArg();

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
use Inpsyde\Assets\ScriptModule;
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
    [
        'handle' => '@my-plugin/module',
        'url' => 'module.js',
        'location' => Asset::FRONTEND,
        'type' => ScriptModule::class,
    ],
];
FILE;
        $filePath = vfsStream::newFile('config.php')
            ->withContent($content)
            ->at($this->root)
            ->url();

        $assets = AssetFactory::createFromFile($filePath);
        static::assertCount(3, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
        static::assertInstanceOf(ScriptModule::class, $assets[2]);
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
            MissingArgumentException::class,
        ];

        yield 'missing url' => [
            [
                'handle' => 'foo',
                'location' => Asset::FRONTEND,
            ],
            MissingArgumentException::class,
        ];

        yield 'missing translation.domain' => [
            [
                'handle' => 'foo',
                'location' => Asset::FRONTEND,
                'url' => 'foo.css',
                'type' => Script::class,
                'translation' => [
                    'no-domain' => 'fail!',
                ],
            ],
            MissingArgumentException::class,
        ];

        yield 'invalid translation type' => [
            [
                'handle' => 'foo',
                'location' => Asset::FRONTEND,
                'url' => 'foo.css',
                'type' => Script::class,
                'translation' => 3.1415,
            ],
            InvalidArgumentException::class,
        ];

        yield 'invalid localization' => [
            [
                'handle' => 'foo',
                'location' => Asset::FRONTEND,
                'url' => 'foo.js',
                'type' => Script::class,
                'localize' => static function () {
                    return 'thisShouldBeAnArray';
                },
            ],
            InvalidArgumentException::class,
        ];
    }

    /**
     * @dataProvider provideConfigWithTranslation
     */
    public function testCreateWithTranslation(array $config, array $expected): void
    {
        /** @var Script $asset */
        $asset = AssetFactory::create($config);
        static::assertSame(
            $expected,
            $asset->translation()
        );
    }

    /**
     * @see testCreateWithTranslation
     */
    public function provideConfigWithTranslation(): array
    {
        return [
            'config with array translation' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'unique-script',
                    'translation' => [
                        'domain' => 'whatever',
                        'path' => 'not/relevant',
                    ],
                ],
                'expected' => [
                    'domain' => 'whatever',
                    'path' => 'not/relevant',
                ],
            ],
            'config with string translation' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'unique-script',
                    'translation' => 'whatever-else',
                ],
                'expected' => [
                    'domain' => 'whatever-else',
                    'path' => null,
                ],
            ],
            'config without translation' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'unique-script',
                ],
                'expected' => [
                    'domain' => '',
                    'path' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideConfigWithLocalize
     */
    public function testCreateWithLocalize(array $config, array $expected): void
    {
        /** @var Script $asset */
        $asset = AssetFactory::create($config);
        static::assertSame(
            $expected,
            $asset->localize()
        );
    }

    /**
     * @see testCreateWithLocalize
     */
    public function provideConfigWithLocalize(): array
    {
        return [
            'localize is array' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'script-with-localize',
                    'localize' => [
                        'SomeObject' => [
                            'propertyOne' => 'someValue',
                        ],
                    ],
                ],
                'expected' => [
                    'SomeObject' => [
                        'propertyOne' => 'someValue',
                    ],
                ],
            ],
            'localize is callable' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'script-with-localize',
                    'localize' => static function () {
                        return [
                            'SomeObject' => [
                                'propertyTwo' => 'someValue',
                            ],
                        ];
                    },
                ],
                'expected' => [
                    'SomeObject' => [
                        'propertyTwo' => 'someValue',
                    ],
                ],
            ],
            'localized value is callable' => [
                'config' => [
                    'type' => Script::class,
                    'url' => 'https://localhost',
                    'handle' => 'script-with-localize',
                    'localize' => [
                        'SomeObject' => static function () {
                            return ['propertyThree' => 'someValue'];
                        },
                    ],
                ],
                'expected' => [
                    'SomeObject' => [
                        'propertyThree' => 'someValue',
                    ],
                ],
            ],
        ];
    }

}
