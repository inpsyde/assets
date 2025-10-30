<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Util\AssetHookResolver;
use Inpsyde\WpContext;

class AssetManagerTest extends AbstractTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('wp_scripts')->justReturn(\Mockery::mock('WP_Scripts'));
        Functions\when('wp_styles')->justReturn(\Mockery::mock('WP_Styles'));
    }

    /**
     * @test
     */
    public function testBasic(): void
    {
        $assetManager = $this->factoryAssetManager();

        static::assertEmpty($assetManager->assets());
        static::assertNotEmpty($assetManager->handlers());
    }

    /**
     * @test
     */
    public function testAssets(): void
    {
        $expectedHandle = 'foo';
        $script = new Script($expectedHandle, '');

        $assetManager = $this->factoryAssetManager();
        $assetManager->register($script);

        $assets = $assetManager->assets();
        static::assertCount(1, $assets);

        $scripts = $assets[Script::class];
        static::assertArrayHasKey($expectedHandle, $scripts);
        static::assertSame($script, $scripts[$expectedHandle]);
    }

    /**
     * @test
     */
    public function testWithHandler(): void
    {
        $assetManager = $this->factoryAssetManager();

        $expectedHandler = new class implements AssetHandler {
            public function register(Asset $asset): bool
            {
                return true;
            }

            public function enqueue(Asset $asset): bool
            {
                return true;
            }
        };

        $assetManager->withHandler('foo', $expectedHandler);

        static::assertSame($expectedHandler, $assetManager->handlers()['foo'] ?? null);
    }

    /**
     * @test
     */
    public function testRegister(): void
    {
        $assetManager = $this->factoryAssetManager();

        $handle = 'foo';

        $myStyle = new class ($handle, '') extends Style {
        };
        $script = new Script($handle, '');

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($myStyle, $script) {
                $manager->register($myStyle, $script);
            });

        static::assertSame($myStyle, $assetManager->asset($handle, Style::class));
        static::assertSame($script, $assetManager->asset($handle, Script::class));
    }

    public function testRegisterSameAssetTypeWithSameHandle(): void
    {
        $assetManager = $this->factoryAssetManager();

        $handle = 'foo';
        $script1 = new Script($handle, '');
        $script2 = new Script($handle, '');

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($script1, $script2) {
                $manager->register($script1, $script2);
            });

        $assets = $assetManager->assets();
        static::assertCount(1, $assets);
    }

    public function testWithAssetExtension(): void
    {
        $handle = 'foo';

        $assetManager = $this->factoryAssetManager();
        $assetManager->extendAsset($handle, Script::class, ['enqueue' => false]);

        $script = new Script($handle, '');
        $script->canEnqueue(true);

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($script) {
                $manager->register($script);
            });

        $asset = $assetManager->asset($handle, Script::class);
        static::assertFalse($asset->enqueue());
        static::assertCount(1, $assetManager->assetExtensions($handle, Script::class));
    }

    public function testWithAssetExtensionInSetupAction(): void
    {
        $handle = 'foo';

        $assetManager = $this->factoryAssetManager();

        $script = new Script($handle, '');
        $script->canEnqueue(true);

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($handle, $script) {
                $manager->extendAsset($handle, Script::class, ['enqueue' => false]);
                $manager->register($script);
            });

        $asset = $assetManager->asset($handle, Script::class);
        static::assertFalse($asset->enqueue());
    }

    public function testWithAssetExtensionAfterSetup(): void
    {
        $handle = 'foo';

        $assetManager = $this->factoryAssetManager();

        $script = new Script($handle, '');
        $script->canEnqueue(true);

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($handle, $script) {
                $manager->register($script);
            });

        $asset = $assetManager->asset($handle, Script::class);

        // Extend the Asset after it is being accessed but before being processed.
        $assetManager->extendAsset($handle, Script::class, ['enqueue' => false]);
        static::assertFalse($asset->enqueue());
    }

    /**
     * @test
     */
    public function testCurrentAssets(): void
    {
        $asset1 = new class ('asset-1', '', Asset::FRONTEND | Asset::BLOCK_ASSETS) extends Script {
        };

        $asset2 = new class ('asset-2', '', Asset::BACKEND) extends Script {
        };

        $assetManager = $this->factoryAssetManager();

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($asset1, $asset2) {
                $manager->register($asset1, $asset2);
            });

        $blockAssets = $assetManager->currentAssets('enqueue_block_assets');
        $frontendAssets = $assetManager->currentAssets('wp_enqueue_scripts');
        $backendAssets = $assetManager->currentAssets('admin_enqueue_scripts');
        $loginAssets = $assetManager->currentAssets('login_enqueue_scripts');
        $invalidHookAssets = $assetManager->currentAssets('undefined_hook');

        static::assertCount(1, $blockAssets);
        static::assertCount(1, $frontendAssets);
        static::assertCount(1, $backendAssets);
        static::assertCount(0, $loginAssets);
        static::assertCount(0, $invalidHookAssets);

        static::assertSame($asset1, $blockAssets[0]);
        static::assertSame($asset1, $frontendAssets[0]);
        static::assertSame($asset2, $backendAssets[0]);
    }

    /**
     * @test
     */
    public function testCurrentAssetsUndefinedHandler(): void
    {
        $asset = new class ('asset', '') extends Style {
        };
        $asset->useHandler(__CLASS__);

        $assetManager = $this->factoryAssetManager();

        Actions\expectDone(AssetManager::ACTION_SETUP)
            ->once()
            ->with($assetManager)
            ->whenHappen(static function (AssetManager $manager) use ($asset) {
                $manager->register($asset);
            });

        $currentAssets = $assetManager->currentAssets('wp_enqueue_scripts');

        static::assertSame([], $currentAssets);
    }

    /**
     * @test
     */
    public function testAsset(): void
    {
        $assetManager = $this->factoryAssetManager();

        $handle = 'asset';

        $asset = new class ($handle, '') extends Style {
        };

        $assetManager->register($asset);

        static::assertSame($asset, $assetManager->asset($handle, get_class($asset)));
        static::assertSame($asset, $assetManager->asset($handle, Style::class));
        static::assertNull($assetManager->asset('undefined handle name', Style::class));
        static::assertNull($assetManager->asset($handle, __CLASS__));
    }

    /**
     * @test
     */
    public function testSetupHappenOnce()
    {
        $assetManager = $this->factoryAssetManager(WpContext::BACKOFFICE);

        Actions\expectAdded('customize_controls_enqueue_scripts')->once();
        Actions\expectAdded('enqueue_block_assets')->once();
        Actions\expectAdded('enqueue_block_editor_assets')->once();
        Actions\expectAdded('admin_enqueue_scripts')->once();

        static::assertTrue($assetManager->setup());
        static::assertFalse($assetManager->setup());
    }

    /**
     * @test
     */
    public function testSetupNoHooksResolved(): void
    {
        $assetManager = $this->factoryAssetManager(WpContext::CRON);

        static::assertFalse($assetManager->setup());
    }

    /**
     * @param string|null $context
     *
     * @return AssetManager
     */
    private function factoryAssetManager(?string $context = null): AssetManager
    {
        $wpContext = WpContext::new()->force($context ?? WpContext::FRONTOFFICE);
        $resolver = new AssetHookResolver($wpContext);

        return new AssetManager($resolver);
    }
}
