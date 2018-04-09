<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Handler\AssetHandler;

class AssetManagerTest extends AbstractTestCase
{

    public function testBasic()
    {
        \Brain\Monkey\Functions\expect('wp_scripts')->once()->andReturn(\Mockery::mock('WP_Scripts'));
        \Brain\Monkey\Functions\expect('wp_styles')->once()->andReturn(\Mockery::mock('WP_Styles'));

        $testee = new AssetManager();

        static::assertInstanceOf(AssetManager::class, $testee);
        static::assertEmpty($testee->assets());

        static::assertEmpty($testee->handlers());

        $testee->useDefaultHandlers();

        static::assertNotEmpty($testee->handlers());
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

        $expectedAsset1 = $this->assetStub('handle1', 'type1');
        $expectedAsset2 = $this->assetStub('handle2', 'type2');

        static::assertSame(
            $testee,
            $testee->register($expectedAsset1, $expectedAsset2)
        );

        static::assertCount(2, $testee->assets());
    }

    public function testSetup()
    {
        $assetWithMatchingHandler = $this->assetStub('handle', Asset::TYPE_SCRIPT);
        $assetUndefinedType = $this->assetStub('handle', 'unknown-type');

        $testee = (new AssetManager())
            ->withHandler(Asset::TYPE_SCRIPT, $this->defaultHandler())
            ->register($assetWithMatchingHandler, $assetUndefinedType);

        Monkey\Actions\expectDone(AssetManager::ACTION_SETUP);

        Monkey\Functions\expect('add_query_arg')->andReturn('');
        Monkey\Functions\expect('is_admin')->andReturn(false);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);

        Monkey\Actions\expectAdded('wp_enqueue_scripts');

        static::assertTrue($testee->setup());
        static::assertFalse($testee->setup());
    }

    public function testSetupAdminAsset()
    {
        $assetWithMatchingHandler = $this->assetStub('handle', Asset::TYPE_ADMIN_SCRIPT);

        $testee = (new AssetManager())
            ->withHandler(Asset::TYPE_ADMIN_SCRIPT, $this->defaultHandler())
            ->register($assetWithMatchingHandler);

        Monkey\Functions\expect('add_query_arg')->andReturn('');
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->never();

        Monkey\Actions\expectAdded('admin_enqueue_scripts');

        static::assertTrue($testee->setup());
    }

    public function testSetupLoginAsset()
    {
        $assetWithMatchingHandler = $this->assetStub('handle', Asset::TYPE_LOGIN_SCRIPT);

        $testee = (new AssetManager())
            ->withHandler(Asset::TYPE_LOGIN_SCRIPT, $this->defaultHandler())
            ->register($assetWithMatchingHandler);

        Monkey\Functions\expect('add_query_arg')->andReturn('wp-login.php');
        Monkey\Functions\expect('is_admin')->never();
        Monkey\Functions\expect('is_customize_preview')->never();

        Monkey\Actions\expectAdded('login_enqueue_scripts');

        static::assertTrue($testee->setup());
    }

    public function testSetupCustomizerAsset()
    {
        $assetWithMatchingHandler = $this->assetStub('handle', Asset::TYPE_CUSTOMIZER_SCRIPT);

        $testee = (new AssetManager())
            ->withHandler(Asset::TYPE_CUSTOMIZER_SCRIPT, $this->defaultHandler())
            ->register($assetWithMatchingHandler);

        Monkey\Functions\expect('add_query_arg')->andReturn('');
        Monkey\Functions\expect('is_admin')->andReturn(false);
        Monkey\Functions\expect('is_customize_preview')->andReturn(true);

        Monkey\Actions\expectAdded('customize_controls_enqueue_scripts');

        static::assertTrue($testee->setup());
    }

    private function assetStub(string $handle, string $type): Asset
    {
        $stub = \Mockery::mock(Asset::class);
        $stub->shouldReceive('handle')->andReturn($handle);
        $stub->shouldReceive('type')->andReturn($type);

        /** @var Asset $stub */
        return $stub;
    }

    private function defaultHandler(): AssetHandler
    {
        $stub = \Mockery::mock(AssetHandler::class);

        /** @var AssetHandler $stub */
        return $stub;
    }
}
