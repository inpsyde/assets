<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ScriptHandlerTest extends AbstractTestCase
{

    public function testBasic()
    {
        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $testee = new ScriptHandler($scriptsStub);

        static::assertInstanceOf(AssetHandler::class, $testee);
        static::assertInstanceOf(OutputFilterAwareAssetHandler::class, $testee);
        static::assertSame('script_loader_tag', $testee->filterHook());
    }

    public function testRegisterEnqueue()
    {
        $expectedHandle = 'handle';
        $expectedData = ['baz' => 'bam'];
        $expectedLoalize = ['foo' => 'bar'];

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn($expectedHandle);
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('inFooter')->andReturnTrue();
        $assetStub->shouldReceive('enqueue')->andReturnTrue();
        $assetStub->shouldReceive('localize')->andReturn($expectedLoalize);
        $assetStub->shouldReceive('data')->andReturn($expectedData);

        Functions\expect('wp_register_script')
            ->once()
            ->with(
                \Mockery::type('string'),
                \Mockery::type('string'),
                \Mockery::type('array'),
                \Mockery::type('string'),
                \Mockery::type('bool')
            );

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

        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $scriptsStub->shouldReceive('add_data')
            ->once()
            ->with(
                $expectedHandle,
                \Mockery::type('string'),
                \Mockery::type('string')
            );

        static::assertTrue((new ScriptHandler($scriptsStub))->enqueue($assetStub));
    }

    public function testEnqueueNotTrue()
    {
        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->shouldReceive('handle')->andReturn('foo');
        $assetStub->shouldReceive('url')->andReturn('url');
        $assetStub->shouldReceive('dependencies')->andReturn([]);
        $assetStub->shouldReceive('version')->andReturn('version');
        $assetStub->shouldReceive('inFooter')->andReturnTrue();
        $assetStub->shouldReceive('localize')->andReturn([]);
        $assetStub->shouldReceive('data')->andReturn([]);
        // enqueue is set to "false", but we're calling ScriptHandler::enqueue
        $assetStub->shouldReceive('enqueue')->andReturnFalse();

        Functions\when('wp_register_script')->justReturn();

        Functions\expect('wp_localize_script')->never();
        Functions\expect('wp_enqueue_script')->never();

        $scriptsStub = \Mockery::mock('\WP_Scripts');

        static::assertFalse((new ScriptHandler($scriptsStub))->enqueue($assetStub));
    }

    public function testFilter()
    {
        $expectedFilterName = 'bar';

        $assetWithoutFilters = \Mockery::mock(Asset::class);
        $assetWithoutFilters->expects('filters')->andReturn([]);

        $assetNonCallableFilter = \Mockery::mock(Asset::class);
        $assetNonCallableFilter->expects('filters')->andReturn(['i am not callable']);

        $assetPreDefinedFilter = \Mockery::mock(Asset::class);
        $assetPreDefinedFilter->expects('filters')->andReturn([$expectedFilterName]);

        $assetCallableFilter = \Mockery::mock(Asset::class);
        $assetCallableFilter->expects('filters')->andReturn(
            [
                function (string $html): string {
                    return $html;
                },
            ]
        );

        $testee = new ScriptHandler(
            \Mockery::mock('\WP_Scripts'),
            [
                $expectedFilterName => function (string $html): string {
                    return $html;
                },
            ]
        );

        \Brain\Monkey\Filters\expectAdded($testee->filterHook());

        static::assertFalse($testee->filter($assetWithoutFilters));
        static::assertFalse($testee->filter($assetNonCallableFilter));
        static::assertTrue($testee->filter($assetPreDefinedFilter));
        static::assertTrue($testee->filter($assetCallableFilter));
    }

    public function testWithOutputFilter()
    {
        $expectedFilterName = 'bar';

        $testee = new ScriptHandler(\Mockery::mock('\WP_Scripts'));

        static::assertCount(2, $testee->outputFilters());
        static::assertArrayHasKey(AsyncScriptOutputFilter::class, $testee->outputFilters());
        static::assertArrayHasKey(DeferScriptOutputFilter::class, $testee->outputFilters());

        static::assertInstanceOf(
            OutputFilterAwareAssetHandler::class,
            $testee->withOutputFilter(
                $expectedFilterName,
                function (string $html) {
                    return $html;
                }
            )
        );
        static::assertArrayHasKey($expectedFilterName, $testee->outputFilters());
    }
}
