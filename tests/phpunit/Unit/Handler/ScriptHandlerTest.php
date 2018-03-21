<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ScriptHandlerTest extends AbstractTestCase
{

    public function testBasic()
    {

        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $testee = new ScriptHandler($scriptsStub);

        static::assertInstanceOf(AssetHandler::class, $testee);
        static::assertSame('script_loader_tag', $testee->outputFilterHook());
    }

    public function testRegister()
    {

        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $testee = new ScriptHandler($scriptsStub);

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->once()->andReturn('handle');
        $assetStub->shouldReceive('url')->once()->andReturn('url');
        $assetStub->shouldReceive('dependencies')->once()->andReturn([]);
        $assetStub->shouldReceive('version')->once()->andReturn('version');
        $assetStub->shouldReceive('inFooter')->once()->andReturnTrue();

        Functions\expect('wp_register_script')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('string'),
                \Mockery::type('array'),
                \Mockery::type('string'),
                \Mockery::type('bool')
            );

        static::assertTrue($testee->register($assetStub));
    }

    public function testEnqueue()
    {

        $expectedHandle = 'handle';
        $expectedLoalize = ['foo' => 'bar'];
        $expectedData = ['baz' => 'bam'];

        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $scriptsStub->shouldReceive('add_data')->once()->with(
            $expectedHandle,
            \Mockery::type('string'),
            \Mockery::type('string')
        );

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn($expectedHandle);
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('inFooter')->andReturnTrue();
        $assetStub->shouldReceive('localize')->andReturn($expectedLoalize);
        $assetStub->shouldReceive('data')->andReturn($expectedData);
        $assetStub->shouldReceive('enqueue')->andReturnTrue();

        Functions\expect('wp_register_script')->once();

        Functions\expect('wp_localize_script')
            ->once()
            ->with(
                $expectedHandle,
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        Functions\expect('wp_enqueue_script')
            ->once()
            ->with($expectedHandle);

        static::assertTrue((new ScriptHandler($scriptsStub))->enqueue($assetStub));
    }
}
