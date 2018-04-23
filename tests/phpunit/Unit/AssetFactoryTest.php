<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey\Actions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Script;

class AssetFactoryTest extends AbstractTestCase
{

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

    public function testConfigMigration()
    {
        $expectedLocation = Asset::FRONTEND;
        $expectedType = Script::class;

        Actions\expectDone('inpsyde.assets.debug')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('array')
            );

        $testee = AssetFactory::create(
            [
                'type' => $expectedLocation,
                'class' => $expectedType,
                'handle' => 'foo',
                'url' => 'bar',
            ]
        );

        static::assertInstanceOf($expectedType, $testee);
        static::assertSame($expectedLocation, $testee->location());
    }
}
