<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpyde\Assets\OutputFilter\AssetOutputFilter;

class AssetManagerTest extends AbstractTestCase
{

    public function testBasic()
    {

        $GLOBALS['wp_scripts'] = \Mockery::mock('WP_Scripts');
        $GLOBALS['wp_styles'] = \Mockery::mock('WP_Styles');

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

        $expectedName = 'foo';
        $expectedFilter = \Mockery::mock(AssetOutputFilter::class);

        static::assertSame($testee, $testee->withOutputFilter($expectedName, $expectedFilter));

        $all = $testee->outputFilters();

        static::assertArrayHasKey($expectedName, $all);
        static::assertSame($expectedFilter, $all[$expectedName]);
    }

    public function testWithHandler()
    {

        $testee = new AssetManager();

        $expectedName = 'foo';
        $expectedHandler = \Mockery::mock(AssetHandler::class);

        static::assertSame($testee, $testee->withHandler($expectedName, $expectedHandler));

        $all = $testee->handlers();

        static::assertArrayHasKey($expectedName, $all);
        static::assertSame($expectedHandler, $all[$expectedName]);
    }

    public function testRegister()
    {

        $testee = new AssetManager();

        $expectedHandle = 'foo';
        $expectedType = 'bar';
        $expectedKey = "{$expectedType}_{$expectedHandle}";

        $expectedAsset = \Mockery::mock(Asset::class);
        $expectedAsset->shouldReceive('handle')->once()->andReturn($expectedHandle);
        $expectedAsset->shouldReceive('type')->once()->andReturn($expectedType);

        static::assertSame($testee, $testee->register($expectedAsset));

        $all = $testee->assets();

        static::assertArrayHasKey($expectedKey, $all);
        static::assertSame($expectedAsset, $all[$expectedKey]);
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
            $testee->registerMultiple(
                [
                    $expectedAsset1,
                    $expectedAsset2,
                ]
            )
        );

        static::assertCount(2, $testee->assets());
    }

    public function testSetup()
    {

        $testee = new AssetManager();


        Monkey\Actions\expectDone(AssetManager::ACTION_SETUP);
        Monkey\Actions\expectAdded('wp_enqueue_scripts');

        static::assertTrue($testee->setup());
        static::assertFalse($testee->setup());
    }
}
