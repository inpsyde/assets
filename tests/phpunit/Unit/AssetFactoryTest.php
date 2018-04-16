<?php

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Script;

class AssetFactoryTest extends AbstractTestCase
{

    public function testCreate()
    {
        $expectedHandle = 'foo';
        $expectedType = Asset::FRONTEND;
        $expectedUrl = 'foo.css';

        $config = [
            'handle' => $expectedHandle,
            'type' => $expectedType,
            'url' => $expectedUrl,
            'class' => Script::class,
        ];

        $asset = AssetFactory::create($config);

        static::assertInstanceOf(Asset::class, $asset);
        static::assertSame($expectedUrl, $asset->url());
        static::assertSame($expectedHandle, $asset->handle());
        static::assertSame($expectedType, $asset->type());
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
                'type' => Asset::FRONTEND,
            ],
        ];

        yield 'missing class' => [
            [
                'url' => 'foo.css',
                'handle' => 'foo',
                'type' => Asset::FRONTEND,
            ],
        ];
    }

    public function testMultipleTypes()
    {
        $expected = Asset::FRONTEND | Asset::BACKEND | Asset::CUSTOMIZER;
        $testee = AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'type' => $expected,
                'class' => Script::class,
            ]
        );

        static::assertSame($expected, $testee->type());
    }

    /**
     * @expectedException  \Inpsyde\Assets\Exception\InvalidArgumentException
     */
    public function testInvalidInstance()
    {
        AssetFactory::create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'type' => Asset::FRONTEND,
                'class' => \stdClass::class,
            ]
        );
    }

    public function testCreateFromFile()
    {
        $output = AssetFactory::createFromFile(__DIR__.'/../../fixtures/asset-config.php');

        static::assertCount(2, $output);
        static::assertInstanceOf(Asset::class, $output[0]);
        static::assertInstanceOf(Asset::class, $output[1]);
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\FileNotFoundException
     */
    public function testCreateFileNotExists()
    {
        AssetFactory::createFromFile('foo');
    }
}
