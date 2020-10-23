<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ScriptHandlerTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testBasic(): void
    {
        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $script = new ScriptHandler($scriptsStub);

        static::assertInstanceOf(AssetHandler::class, $script);
        static::assertInstanceOf(OutputFilterAwareAssetHandler::class, $script);
        static::assertSame('script_loader_tag', $script->filterHook());
    }

    /**
     * @test
     */
    public function testRegisterEnqueue(): void
    {
        $data = ['baz' => 'bam'];
        $localize = ['foo' => 'bar'];
        $inline = ['before' => 'before()', 'after' => 'after()'];

        $script = (new Script('handle', 'url', Asset::FRONTEND, ['data' => $data]))
            ->withVersion('version')
            ->isInFooter()
            ->withTranslation('i10n', 'i10n.json')
            ->prependInlineScript($inline['before'])
            ->appendInlineScript($inline['after'])
            ->withLocalize('localize', $localize);

        Functions\expect('wp_register_script')
            ->once()
            ->andReturnUsing(
                static function (
                    string $handle,
                    string $src,
                    array $deps,
                    string $ver,
                    bool $footer
                ): bool {
                    static::assertSame('handle', $handle);
                    static::assertSame('url', $src);
                    static::assertSame([], $deps);
                    static::assertSame('version', $ver);
                    static::assertTrue($footer);

                    return true;
                }
            );

        Functions\expect('wp_add_inline_script')
            ->twice()
            ->andReturnUsing(
                static function (string $handle, string $code, string $where) use ($inline): void {
                    static::assertSame('handle', $handle);
                    static::assertContains($where, ['before', 'after']);
                    static::assertSame($inline[$where], $code);
                }
            );

        Functions\expect('wp_set_script_translations')
            ->once()
            ->andReturnUsing(
                static function (string $handle, string $domain, string $path) {
                    static::assertSame('handle', $handle);
                    static::assertSame('i10n', $domain);
                    static::assertSame('i10n.json', $path);
                }
            );

        Functions\expect('wp_localize_script')
            ->once()
            ->andReturnUsing(
                static function (string $handle, string $name, array $data) use ($localize): void {
                    static::assertSame('handle', $handle);
                    static::assertSame('localize', $name);
                    static::assertSame($localize, $data);
                }
            );

        Functions\expect('wp_enqueue_script')->once()->with('handle');

        $scriptsStub = \Mockery::mock('\WP_Scripts');
        $scriptsStub->shouldReceive('add_data')
            ->once()
            ->andReturnUsing(
                static function (string $handle, string $key, string $value) use ($data): void {
                    static::assertSame('handle', $handle);
                    static::assertSame($key, key($data));
                    static::assertSame($value, reset($data));
                }
            );

        static::assertTrue((new ScriptHandler($scriptsStub))->enqueue($script));
    }

    /**
     * @test
     */
    public function testEnqueueNotTrue(): void
    {
        $script = (new Script('handle', 'url'))->canEnqueue('__return_false');

        Functions\when('wp_register_script')->justReturn();
        Functions\expect('wp_localize_script')->never();
        Functions\expect('wp_enqueue_script')->never();

        $scriptsStub = \Mockery::mock('\WP_Scripts');

        static::assertFalse((new ScriptHandler($scriptsStub))->enqueue($script));
    }

    /**
     * @test
     */
    public function testFilter(): void
    {
        $return = static function (string $html): string {
            return $html;
        };

        $scriptNoFilters = new Script('a', '');
        $scriptFilterNotCallable = (new Script('b', ''))->withFilters('do not call me');
        $scriptFilterCallable = (new Script('c', ''))->withFilters($return);
        $scriptFilterDefault = (new Script('d', ''))->withFilters(__METHOD__);

        $script = new ScriptHandler(
            \Mockery::mock('\WP_Scripts'),
            [__METHOD__ => $return]
        );

        Filters\expectAdded($script->filterHook())
            ->twice()
            ->whenHappen(
                static function (callable $callable, string $handle) use ($return): void {
                    $html = random_bytes(8);
                    static::assertSame($return($html), $callable($html, $handle));
                }
            );

        static::assertFalse($script->filter($scriptNoFilters));
        static::assertFalse($script->filter($scriptFilterNotCallable));
        static::assertTrue($script->filter($scriptFilterCallable));
        static::assertTrue($script->filter($scriptFilterDefault));
    }

    /**
     * @test
     */
    public function testWithOutputFilter(): void
    {
        $script = new ScriptHandler(\Mockery::mock('\WP_Scripts'));

        $filters = $script->outputFilters();

        static::assertInstanceOf(
            AsyncScriptOutputFilter::class,
            $filters[AsyncScriptOutputFilter::class]
        );

        static::assertInstanceOf(
            DeferScriptOutputFilter::class,
            $filters[DeferScriptOutputFilter::class]
        );

        static::assertInstanceOf(
            InlineAssetOutputFilter::class,
            $filters[InlineAssetOutputFilter::class]
        );

        $custom = static function (string $html) {
            return $html;
        };

        $script->withOutputFilter('custom', $custom);

        static::assertSame($custom, $script->outputFilters()['custom']);
    }
}
