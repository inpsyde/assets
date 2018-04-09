<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class StyleHandlerTest extends AbstractTestCase
{

    public function testBasic()
    {
        $stylesStub = \Mockery::mock('\WP_Styles');
        $testee = new StyleHandler($stylesStub);

        static::assertInstanceOf(AssetHandler::class, $testee);
        static::assertInstanceOf(OutputFilterAwareAssetHandler::class, $testee);
        static::assertSame('style_loader_tag', $testee->filterHook());
    }

    public function testRegister()
    {
        $stylesStub = \Mockery::mock('\WP_Styles');
        $testee = new StyleHandler($stylesStub);

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->once()->andReturn('handle');
        $assetStub->shouldReceive('url')->once()->andReturn('url');
        $assetStub->shouldReceive('dependencies')->once()->andReturn([]);
        $assetStub->shouldReceive('version')->once()->andReturn('version');
        $assetStub->shouldReceive('media')->once()->andReturn('media');

        Functions\expect('wp_register_style')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('string'),
                \Mockery::type('array'),
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        static::assertTrue($testee->register($assetStub));
    }

    public function testEnqueue()
    {
        $expectedHandle = 'handle';
        $expectedData = ['baz' => 'bam'];

        $stylesStub = \Mockery::mock('\WP_Styles');
        $stylesStub->shouldReceive('add_data')
            ->once()
            ->with(
                $expectedHandle,
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn('handle');
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('media')->andReturn('media');
        $assetStub->shouldReceive('data')->andReturn($expectedData);
        $assetStub->shouldReceive('enqueue')->andReturnTrue();

        Functions\expect('wp_register_style')->once();

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with($expectedHandle);

        static::assertTrue((new StyleHandler($stylesStub))->enqueue($assetStub));
    }
}
