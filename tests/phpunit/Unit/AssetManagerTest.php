<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;

class AssetManagerTest extends AbstractTestCase
{

    public function testBasic()
    {

        \Brain\Monkey\Functions\expect('wp_scripts')->once()->andReturn(\Mockery::mock('WP_Scripts'));
        \Brain\Monkey\Functions\expect('wp_styles')->once()->andReturn(\Mockery::mock('WP_Styles'));

        $testee = new AssetManager();

        static::assertInstanceOf(AssetManager::class, $testee);
        static::assertEmpty($testee->assets());

        static::assertEmpty($testee->outputFilters());
        static::assertEmpty($testee->handlers());

        $testee->useDefaultOutputFilters()->useDefaultHandlers();

        static::assertNotEmpty($testee->outputFilters());
        static::assertNotEmpty($testee->handlers());
    }

    public function testWithOutputFilter()
    {

        $testee = new AssetManager();

        $expectedName   = 'foo';
        $expectedFilter = \Mockery::mock(AssetOutputFilter::class);

        static::assertSame($testee, $testee->withOutputFilter($expectedName, $expectedFilter));

        $all = $testee->outputFilters();

        static::assertArrayHasKey($expectedName, $all);
        static::assertSame($expectedFilter, $all[ $expectedName ]);
    }

    public function testWithHandler()
    {

        $testee = new AssetManager();

        $expectedName    = 'foo';
        $expectedHandler = \Mockery::mock(AssetHandler::class);

        static::assertSame($testee, $testee->withHandler($expectedName, $expectedHandler));

        $all = $testee->handlers();

        static::assertArrayHasKey($expectedName, $all);
        static::assertSame($expectedHandler, $all[ $expectedName ]);
    }

    public function testRegister()
    {

        $testee = new AssetManager();

        $expectedHandle = 'foo';
        $expectedType   = 'bar';
        $expectedKey    = "{$expectedType}_{$expectedHandle}";

        $expectedAsset = \Mockery::mock(Asset::class);
        $expectedAsset->shouldReceive('handle')->once()->andReturn($expectedHandle);
        $expectedAsset->shouldReceive('type')->once()->andReturn($expectedType);

        static::assertSame($testee, $testee->register($expectedAsset));

        $all = $testee->assets();

        static::assertArrayHasKey($expectedKey, $all);
        static::assertSame($expectedAsset, $all[ $expectedKey ]);
    }

    public function testRegisterMultiple()
    {

        $testee = new AssetManager();

        $expectedAsset1 = \Mockery::mock(Asset::class);
        $expectedAsset1->shouldReceive('handle')->once()->andReturn('handle1');
        $expectedAsset1->shouldReceive('type')->once()->andReturn('type1');

        $expectedAsset2 = \Mockery::mock(Asset::class);
        $expectedAsset2->shouldReceive('handle')->once()->andReturn('handle2');
        $expectedAsset2->shouldReceive('type')->once()->andReturn('type2');

        static::assertSame(
            $testee,
            $testee->register($expectedAsset1, $expectedAsset2)
        );

        static::assertCount(2, $testee->assets());
    }

    public function testSetup()
    {

        $expectedHandler = \Mockery::mock(AssetHandler::class);
        $expectedHandler->shouldReceive('enqueue')->once();
        $expectedHandler->shouldReceive('outputFilterHook')->once()->andReturn('foo');

        $assetWithMatchingHandler = \Mockery::mock(Asset::class);
        $assetWithMatchingHandler->shouldReceive('handle')->andReturn('handle');
        $assetWithMatchingHandler->shouldReceive('type')->andReturn(Asset::TYPE_SCRIPT);
        $assetWithMatchingHandler->shouldReceive('filters')->andReturn([]);

        $assetUndefinedType = \Mockery::mock(Asset::class);
        $assetUndefinedType->shouldReceive('handle')->andReturn('handle');
        $assetUndefinedType->shouldReceive('type')->andReturn('unknown-type');
        $assetUndefinedType->shouldReceive('filters')->never();

        $testee = (new AssetManager())
            ->withHandler(Asset::TYPE_SCRIPT, $expectedHandler)
            ->register($assetWithMatchingHandler, $assetUndefinedType);


        Monkey\Actions\expectDone(AssetManager::ACTION_SETUP);

        static::assertTrue($testee->setup());
        static::assertFalse($testee->setup());
    }
}
