<?php

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class AssetFactoryTest extends AbstractTestCase
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

    public function testBasic()
    {
        $expectedHandle = 'foo';
        $expectedType = Script::class;
        $expectedUrl = 'foo.js';

        $testee = AssetFactory::create(
            [
                'handle' => $expectedHandle,
                'url' => $expectedUrl,
                'type' => $expectedType,
            ]
        );

        static::assertInstanceOf(Asset::class, $testee);
        static::assertInstanceOf($expectedType, $testee);
        static::assertSame($expectedUrl, $testee->url());
        static::assertSame($expectedHandle, $testee->handle());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }

    public function testCreateLocation()
    {
        $expectedLocation = Asset::BACKEND;

        $testee = AssetFactory::create(
            [
                'handle' => 'foo',
                'location' => $expectedLocation,
                'url' => 'foo.js',
                'type' => Script::class,
            ]
        );

        static::assertSame($expectedLocation, $testee->location());
    }

    public function testCreateMultipleLocations()
    {
        $expected = Asset::FRONTEND | Asset::BACKEND | Asset::CUSTOMIZER;
        $testee = AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => $expected,
                'type' => Script::class,
            ]
        );

        static::assertSame($expected, $testee->location());
    }

    /**
     * @param array $config
     *
     * @dataProvider provideInvalidConfig
     *
     * @expectedException \Inpsyde\Assets\Exception\MissingArgumentException
     * @throws \Inpsyde\Assets\Exception\InvalidArgumentException
     */
    public function testInvalidConfig(array $config)
    {
        AssetFactory::create($config);
    }

    public function provideInvalidConfig()
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

    /**
     * @expectedException  \Inpsyde\Assets\Exception\InvalidArgumentException
     */
    public function testInvalidType()
    {
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
     * @throws \Throwable
     * @deprecated Loader\PhpArrayFileLoaderTest
     */
    public function testCreateFromFile()
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
     * @deprecated Loader\PhpArrayLoaderTest
     */
    public function testCreateFromArray()
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
}
