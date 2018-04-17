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

    public function testRegisterEnqueue()
    {
        $expectedHandle = 'handle';
        $expectedData = ['baz' => 'bam'];

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn($expectedHandle);
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('media')->andReturn('media');
        $assetStub->shouldReceive('data')->andReturn($expectedData);
        $assetStub->shouldReceive('enqueue')->andReturnTrue();

        Functions\expect('wp_register_style')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('string'),
                \Mockery::type('array'),
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        Functions\expect('wp_enqueue_style')
            ->once()
            ->with($expectedHandle);

        $stylesStub = \Mockery::mock('\WP_Styles');
        $stylesStub->shouldReceive('add_data')
            ->once()
            ->with(
                $expectedHandle,
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        static::assertTrue((new StyleHandler($stylesStub))->enqueue($assetStub));
    }

    public function testEnqueueNotTrue()
    {
        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn('handle');
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('media')->andReturn('media');
        $assetStub->shouldReceive('data')->andReturn([]);
        // enqueue is set to "false", but we're calling StyleHandler::enqueue
        $assetStub->shouldReceive('enqueue')->andReturnFalse();

        Functions\when('wp_register_style')->justReturn();
        Functions\expect('wp_enqueue_style')->never();

        $scriptsStub = \Mockery::mock('\WP_Styles');

        static::assertFalse((new StyleHandler($scriptsStub))->enqueue($assetStub));
    }
}
