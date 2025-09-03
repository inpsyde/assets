<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\Handler;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptModuleHandler;
use Inpsyde\Assets\ScriptModule;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ScriptModuleHandlerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testBasic(): void
    {
        $handler = new ScriptModuleHandler();

        static::assertInstanceOf(AssetHandler::class, $handler);
    }

    /**
     * @test
     */
    public function testRegisterEnqueue(): void
    {
        $handler = new class extends ScriptModuleHandler {
            public static function scriptModulesSupported(): bool
            {
                return true;
            }
        };

        $scriptModule = (new ScriptModule('@my-plugin/module', 'module.js'))
            ->withVersion('1.0.0')
            ->withDependencies('@wordpress/interactivity', '@wordpress/element');

        Functions\expect('wp_register_script_module')
            ->once()
            ->andReturnUsing(
                static function (
                    string $id,
                    string $src,
                    array $deps,
                    ?string $version
                ): void {
                    static::assertSame('@my-plugin/module', $id);
                    static::assertSame('module.js', $src);
                    static::assertSame(['@wordpress/interactivity', '@wordpress/element'], $deps);
                    static::assertSame('1.0.0', $version);
                }
            );

        Functions\expect('wp_enqueue_script_module')
            ->once()
            ->with('@my-plugin/module');

        $result = $handler->enqueue($scriptModule);

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function testRegisterEnqueueWithoutSupport(): void
    {
        $handler = new class extends ScriptModuleHandler {
            public static function scriptModulesSupported(): bool
            {
                return false;
            }
        };

        $scriptModule = (new ScriptModule('@my-plugin/module', 'module.js'))
            ->withVersion('1.0.0')
            ->withDependencies('@wordpress/interactivity');

        Functions\expect('wp_register_script_module')->never();
        Functions\expect('wp_enqueue_script_module')->never();

        $result = $handler->enqueue($scriptModule);

        static::assertFalse($result);
    }

}