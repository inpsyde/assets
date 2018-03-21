<?php

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;

class AssetFactoryTest extends AbstractTestCase
{

    public function testCreate()
    {

        $testee = new AssetFactory();

        $expectedHandle = 'foo';
        $expectedType = Asset::TYPE_STYLE;
        $expectedUrl = 'foo.css';

        $config = [
            'handle' => $expectedHandle,
            'type' => $expectedType,
            'url' => $expectedUrl,
        ];

        $asset = $testee->create($config);

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

        (new AssetFactory())->create($config);
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
                'type' => Asset::TYPE_STYLE,
            ],
        ];

        yield 'missing handle' => [
            [
                'url' => 'foo.css',
                'type' => Asset::TYPE_STYLE,
            ],
        ];
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\InvalidArgumentException
     */
    public function testInvalidType()
    {

        (new AssetFactory())->create(
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'type' => 'non-existing-type',
            ]
        );
    }

    public function testCreateFromFile()
    {

        $output = (new AssetFactory())->createFromFile(__DIR__.'/../../fixtures/asset-config.php');

        static::assertCount(2, $output);
        static::assertInstanceOf(Asset::class, $output[0]);
        static::assertInstanceOf(Asset::class, $output[1]);
    }

    /**
     * @expectedException \Inpsyde\Assets\Exception\FileNotFoundException
     */
    public function testCreateFileNotExists()
    {

        (new AssetFactory())->createFromFile('foo');
    }
}
