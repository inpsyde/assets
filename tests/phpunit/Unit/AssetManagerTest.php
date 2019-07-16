<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetHookResolver;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Handler\AssetHandler;
use function Brain\Monkey\Functions\expect;

class AssetManagerTest extends AbstractTestCase
{

    public function testBasic()
    {
        expect('wp_scripts')->once()->andReturn(\Mockery::mock('WP_Scripts'));
        expect('wp_styles')->once()->andReturn(\Mockery::mock('WP_Styles'));

        $testee = new AssetManager(\Mockery::mock(AssetHookResolver::class));

        static::assertInstanceOf(AssetManager::class, $testee);
        static::assertEmpty($testee->assets());

        static::assertEmpty($testee->handlers());

        $testee->useDefaultHandlers();

        static::assertNotEmpty($testee->handlers());
    }

    public function testWithHandler()
    {
        $testee = new AssetManager(\Mockery::mock(AssetHookResolver::class));

        $expectedName = 'foo';
        $expectedHandler = \Mockery::mock(AssetHandler::class);

        static::assertSame($testee, $testee->withHandler($expectedName, $expectedHandler));

        $all = $testee->handlers();

        static::assertArrayHasKey($expectedName, $all);
        static::assertSame($expectedHandler, $all[$expectedName]);
    }

    public function testRegister()
    {
        $testee = new AssetManager(\Mockery::mock(AssetHookResolver::class));

        $expectedHandle = 'foo';

        $expectedAsset = \Mockery::mock(Asset::class);
        $expectedAsset->expects('handle')->andReturn($expectedHandle);
        static::assertSame($testee, $testee->register($expectedAsset));

        $asset = $testee->asset($expectedHandle, get_class($expectedAsset));
        static::assertSame($expectedAsset, $asset);
    }

    public function testCurrentAssets()
    {
        $expectedHandlerName = 'foo';
        $expectedHandle = 'bar';

        $expectedAsset = \Mockery::mock(Asset::class);
        $expectedAsset->expects('handle')->andReturn($expectedHandle);
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
        $expectedHandle = 'bar';

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->expects('handle')->andReturn($expectedHandle);
        $assetStub->expects('handler')->twice()->andReturn($expectedHandlerName);
        $assetStub->expects('location')->twice()->andReturn(Asset::BACKEND | Asset::FRONTEND);

        $testee = (new AssetManager())
            ->withHandler($expectedHandlerName, \Mockery::mock(AssetHandler::class))
            ->register($assetStub);

        static::assertCount(1, $testee->currentAssets('wp_enqueue_scripts'));
        static::assertCount(1, $testee->currentAssets('admin_enqueue_scripts'));
    }

    public function testCurrentAssetDifferentHook()
    {
        $expectedHandlerName = 'foo';
        $expectedHandle = 'bar';

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->expects('handle')->andReturn($expectedHandle);
        $assetStub->expects('handler')->andReturn($expectedHandlerName);
        $assetStub->expects('location')->andReturn(Asset::BACKEND);

        $testee = (new AssetManager())
            ->withHandler($expectedHandlerName, \Mockery::mock(AssetHandler::class))
            ->register($assetStub);

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
        $expectedHandle = 'bar';

        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->expects('handle')->andReturn($expectedHandle);
        $assetStub->expects('handler')->andReturn('undefined');
        $assetStub->expects('location')->never();

        static::assertEmpty(
            (new AssetManager())
                ->register($assetStub)
                ->currentAssets('wp_enqueue_scripts')
        );
    }

    public function testRegisterMultiple()
    {
        $testee = new AssetManager(\Mockery::mock(AssetHookResolver::class));

        $expectedAsset1 = $this->assetStub('handle1', 'type1');
        $expectedAsset2 = $this->assetStub('handle2', 'type2');

        static::assertSame(
            $testee,
            $testee->register($expectedAsset1, $expectedAsset2)
        );

        static::assertSame($expectedAsset1, $testee->asset('handle1', get_class($expectedAsset1)));
        static::assertSame($expectedAsset2, $testee->asset('handle2', get_class($expectedAsset2)));
    }

    public function testAsset()
    {
        $testee = new AssetManager(\Mockery::mock(AssetHookResolver::class));

        $expectedHandle = 'bar';
        $assetStub = \Mockery::mock(Asset::class);
        $assetStub->expects('handle')->andReturn($expectedHandle);

        $testee->register($assetStub);

        static::assertSame($assetStub, $testee->asset($expectedHandle, get_class($assetStub)));
        static::assertNull($testee->asset('undefined handle name', get_class($assetStub)));
        static::assertNull($testee->asset($expectedHandle, 'some undefined class type'));
    }

    public function testSetup()
    {
        $resolverStub = \Mockery::mock(AssetHookResolver::class);
        $resolverStub->expects('resolve')->andReturn([Asset::HOOK_FRONTEND]);

        Monkey\Actions\expectAdded(Asset::HOOK_FRONTEND);

        $testee = (new AssetManager($resolverStub))
            ->withHandler(Asset::FRONTEND, $this->defaultHandler())
            ->register($this->assetStub('handle', Asset::FRONTEND));

        static::assertTrue($testee->setup());
        static::assertFalse($testee->setup());
    }

    public function testSetupNoHooksResolved()
    {
        $resolverStub = \Mockery::mock(AssetHookResolver::class);
        $resolverStub->expects('resolve')->andReturn([]);

        $testee = new AssetManager($resolverStub);

        static::assertFalse($testee->setup());
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
