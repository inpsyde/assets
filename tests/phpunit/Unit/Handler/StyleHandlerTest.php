<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class StyleHandlerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic(): void
    {
        $stylesStub = \Mockery::mock('\WP_Styles');
        $handler = new StyleHandler($stylesStub);

        static::assertSame('style_loader_tag', $handler->filterHook());
    }

    /**
     * @test
     */
    public function testRegisterEnqueue(): void
    {
        $data = ['baz' => 'bam'];

        $style = new Style('handle', 'url', Asset::FRONTEND);
        $style
            ->withVersion('version')
            ->forMedia('media')
            ->withInlineStyles('x')
            ->withData($data);

        Functions\expect('wp_register_style')
            ->once()
            ->andReturnUsing(
                static function (
                    string $handle,
                    string $src,
                    array $deps,
                    string $ver,
                    string $media
                ): bool {
                    static::assertSame('handle', $handle);
                    static::assertSame('url', $src);
                    static::assertSame([], $deps);
                    static::assertSame('version', $ver);
                    static::assertSame('media', $media);

                    return true;
                }
            );

        Functions\expect('wp_add_inline_style')->once();
        Functions\expect('wp_enqueue_style')->once()->with('handle');

        $stylesStub = \Mockery::mock('\WP_Styles');
        $stylesStub->shouldReceive('add_data')
            ->once()
            ->andReturnUsing(
                static function (string $handle, string $key, string $value) use ($data): void {
                    static::assertSame('handle', $handle);
                    static::assertSame($key, key($data));
                    static::assertSame($value, reset($data));
                }
            );

        static::assertTrue((new StyleHandler($stylesStub))->enqueue($style));
    }

    /**
     * @test
     */
    public function testEnqueueNotTrue(): void
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
        $assetStub->shouldReceive('inlineStyles')->andReturn(null);
        $assetStub->shouldReceive('cssVars')->andReturn([]);

        Functions\when('wp_register_style')->justReturn();
        Functions\expect('wp_enqueue_style')->never();

        $scriptsStub = \Mockery::mock('\WP_Styles');

        static::assertFalse((new StyleHandler($scriptsStub))->enqueue($assetStub));
    }
}
