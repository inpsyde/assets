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

        $expectedAsset = \Mockery::mock(Asset::class);
        static::assertSame($testee, $testee->register($expectedAsset));

        $all = $testee->assets();

        static::assertSame($expectedAsset, $all[0]);
        static::assertCount(1, $all);
    }

    public function testCurrentAssets()
    {
        $expectedHandlerName = 'foo';

        $expectedAsset = \Mockery::mock(Asset::class);
        $expectedAsset->expects('handler')->andReturn($expectedHandlerName);
        $expectedAsset->expects('location')->andReturn(Asset::FRONTEND);

        $testee = (new AssetManager())
            ->withHandler($expectedHandlerName, \Mockery::mock(AssetHandler::class))
            ->register($expectedAsset);

        static::assertCount(1, $testee->currentAssets('wp_enqueue_scripts'));
    }

    public function testCurrentAssetMultipleTypes()
    {
        $expectedHandlerName = 'foo';

        $assetMultipleTypes = \Mockery::mock(Asset::class);
        $assetMultipleTypes->expects('handler')->twice()->andReturn($expectedHandlerName);
        $assetMultipleTypes->expects('location')->twice()->andReturn(Asset::BACKEND | Asset::FRONTEND);

        $testee = (new AssetManager())
            ->withHandler($expectedHandlerName, \Mockery::mock(AssetHandler::class))
            ->register($assetMultipleTypes);

        static::assertCount(1, $testee->currentAssets('wp_enqueue_scripts'));
        static::assertCount(1, $testee->currentAssets('admin_enqueue_scripts'));
    }

    public function testCurrentAssetDifferentHook()
    {
        $expectedHandlerName = 'foo';

        $assetMultipleTypes = \Mockery::mock(Asset::class);
        $assetMultipleTypes->expects('handler')->andReturn($expectedHandlerName);
        $assetMultipleTypes->expects('location')->andReturn(Asset::BACKEND);

        $testee = (new AssetManager())
            ->withHandler($expectedHandlerName, \Mockery::mock(AssetHandler::class))
            ->register($assetMultipleTypes);

        // ask for assets in frontend, but only Asset for Backend is registered.
        static::assertCount(0, $testee->currentAssets('wp_enqueue_scripts'));
    }

    public function testCurrentAssetsUndefinedHook()
    {
        static::assertEmpty(
            (new AssetManager())->currentAssets('undefined_hook')
        );
    }

    public function testCurrentAssetsUndefinedHandler()
    {
        $assetNonMatchingHandler = \Mockery::mock(Asset::class);
        $assetNonMatchingHandler->expects('handler')->andReturn('undefined');
        $assetNonMatchingHandler->expects('location')->never();

        static::assertEmpty(
            (new AssetManager())
                ->register($assetNonMatchingHandler)
                ->currentAssets('wp_enqueue_scripts')
        );
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
        Monkey\Functions\expect('is_admin')->andReturn(false);
        Monkey\Functions\expect('is_customize_preview')->never();
        Monkey\Functions\expect('wp_doing_ajax')->never();

        Monkey\Actions\expectAdded('wp_enqueue_scripts');

        $testee = $this->setupTestee(Asset::FRONTEND);
        static::assertTrue($testee->setup());
        static::assertFalse($testee->setup());
    }

    public function testSetupAdminAsset()
    {
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(false);

        Monkey\Actions\expectAdded('admin_enqueue_scripts');

        $testee = $this->setupTestee(Asset::BACKEND);
        static::assertTrue($testee->setup());
    }

    public function testSetupAjaxAsset()
    {
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(true);

        $testee = $this->setupTestee(Asset::FRONTEND);
        static::assertFalse($testee->setup());
    }

    public function testSetupLoginAsset()
    {
        Monkey\Functions\expect('is_admin')->never();
        Monkey\Functions\expect('is_customize_preview')->never();
        Monkey\Functions\expect('wp_doing_ajax')->never();

        Monkey\Actions\expectAdded('login_enqueue_scripts');

        $cur = $GLOBALS['pagenow'];
        $GLOBALS['pagenow'] = 'wp-login.php';

        $testee = $this->setupTestee(Asset::LOGIN);
        static::assertTrue($testee->setup());

        // restor global var if exist.
        $GLOBALS['pagenow'] = $cur;
    }

    public function testSetupCustomizerAsset()
    {
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(true);
        Monkey\Functions\expect('wp_doing_ajax')->never();

        Monkey\Actions\expectAdded('customize_controls_enqueue_scripts');

        $testee = $this->setupTestee(Asset::CUSTOMIZER);
        static::assertTrue($testee->setup());
    }

    private function setupTestee(string $type): AssetManager
    {
        return (new AssetManager())
            ->withHandler($type, $this->defaultHandler())
            ->register($this->assetStub('handle', $type));
    }

    private function assetStub(string $handle, string $type): Asset
    {
        $stub = \Mockery::mock(Asset::class);
        $stub->shouldReceive('handle')->andReturn($handle);
        $stub->shouldReceive('location')->andReturn($type);

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
