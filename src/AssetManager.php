<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Handler\ScriptModuleHandler;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\Util\AssetHookResolver;

/**
 * @phpstan-import-type AssetExtensionConfig from AssetFactory
 */
final class AssetManager
{
    public const ACTION_SETUP = 'inpsyde.assets.setup';

    /**
     * Contains the state of the AssetManager, where keys are hook names that are already added
     * to avoid add them more than once.
     *
     * @var array<string, bool>
     */
    private array $hooksAdded = [];

    /**
     * @var array<
     *      Style::class|Script::class|ScriptModule::class,
     *      array<string, AssetExtensionConfig>
     * >
     */
    private array $extensions = [];

    /**
     * @var array<Style::class|Script::class|ScriptModule::class, array<string, bool>>
     */
    private array $processedAssets = [];

    private AssetCollection $assets;

    /**
     * @var array<AssetHandler>
     */
    private array $handlers = [];

    private AssetHookResolver $hookResolver;

    private bool $setupDone = false;

    /**
     * @param AssetHookResolver|null $hookResolver
     */
    public function __construct(?AssetHookResolver $hookResolver = null)
    {
        $this->hookResolver = $hookResolver ?? new AssetHookResolver();
        $this->assets = new AssetCollection();
    }

    /**
     * @return static
     */
    public function useDefaultHandlers(): AssetManager
    {
        $this->handlers[StyleHandler::class] ??= new StyleHandler(wp_styles());
        $this->handlers[ScriptHandler::class] ??= new ScriptHandler(wp_scripts());
        $this->handlers[ScriptModuleHandler::class] ??= new ScriptModuleHandler();

        return $this;
    }

    /**
     * @param string $name
     * @param AssetHandler $handler
     *
     * @return static
     */
    public function withHandler(string $name, AssetHandler $handler): AssetManager
    {
        $this->handlers[$name] = $handler;

        return $this;
    }

    /**
     * @return array<AssetHandler>
     */
    public function handlers(): array
    {
        return $this->handlers;
    }

    /**
     * @param Asset $asset
     * @param Asset ...$assets
     *
     * @return static
     */
    public function register(Asset $asset, Asset ...$assets): AssetManager
    {
        array_unshift($assets, $asset);

        foreach ($assets as $asset) {
            $this->extendAndRegisterAsset($asset);
        }

        return $this;
    }

    /**
     * @return array<string, array<string, Asset>>
     */
    public function assets(): array
    {
        $this->ensureSetup();

        return $this->assets->all();
    }

    /**
     * Retrieve an `Asset` instance by a given asset handle and type (class).
     *
     * @param string $handle
     * @param class-string|null $type   Deprecated, will be in future not nullable.
     *
     * @return Asset|null
     */
    public function asset(string $handle, ?string $type = null): ?Asset
    {
        $this->ensureSetup();

        if ($type === null) {
            return $this->assets->getFirst($handle);
        }

        return $this->assets->get($handle, $type);
    }

    /**
     * @param string $handle
     * @param string $type
     * @param AssetExtensionConfig $extensions
     *
     * @return $this
     */
    public function extendAsset(string $handle, string $type, array $extensions): AssetManager
    {
        $existingExtension = $this->extensions[$type][$handle] ?? [];
        $extensions = array_merge_recursive($existingExtension, $extensions);
        $this->extensions[$type][$handle] = $extensions;

        // In case, the asset is already registered,
        // but not yet processed, extend it.
        $asset = $this->assets->get($handle, $type);
        if ($asset !== null && !$this->isAssetProcessed($asset)) {
            $this->extendAndRegisterAsset($asset);
        }

        return $this;
    }

    /**
     * @param string $handle
     * @param string $type
     *
     * @return AssetExtensionConfig
     */
    public function assetExtensions(string $handle, string $type): array
    {
        return $this->extensions[$type][$handle] ?? [];
    }

    /**
     * @param Asset $asset
     *
     * @return $this
     */
    protected function extendAndRegisterAsset(Asset $asset): AssetManager
    {
        $handle = $asset->handle();
        $type = get_class($asset);
        $extensions = $this->assetExtensions($handle, $type);
        if (count($extensions) > 0 && !$this->isAssetProcessed($asset)) {
            $asset = AssetFactory::configureAsset($asset, $extensions);
        }

        $this->assets->add($asset);

        return $this;
    }

    /**
     * @return bool
     */
    public function setup(): bool
    {
        $hooksAdded = 0;

        /**
         * It is possible to execute AssetManager::setup() at a specific hook to only process assets
         * specific of that hook.
         *
         * E.g. `add_action('enqueue_block_editor_assets', [new AssetManager, 'setup']);`
         *
         * `$this->hookResolver->resolve()` will return current hook if it is one of the assets
         * enqueuing hook.
         */
        foreach ($this->hookResolver->resolve() as $hook) {
            // If the hook was already added, or it is in the past, don't bother adding.
            if (!empty($this->hooksAdded[$hook]) || (did_action($hook) && !doing_action($hook))) {
                continue;
            }

            $hooksAdded++;
            $this->hooksAdded[$hook] = true;

            add_action(
                $hook,
                function () use ($hook) {
                    $this->processAssets($hook);
                }
            );
        }

        return $hooksAdded > 0;
    }

    /**
     * Returning all matching assets to given hook.
     *
     * @param string $currentHook
     *
     * @return array<Asset>
     */
    public function currentAssets(string $currentHook): array
    {
        return $this->loopCurrentHookAssets($currentHook, false);
    }

    /**
     * @param string $currentHook
     *
     * @return void
     */
    private function processAssets(string $currentHook): void
    {
        $this->loopCurrentHookAssets($currentHook, true);
    }

    /**
     * @param string $currentHook
     * @param bool $process
     *
     * @return array<Asset>
     *
     * phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     */
    private function loopCurrentHookAssets(string $currentHook, bool $process): array
    {
        $this->ensureSetup();
        if (count($this->assets->all()) < 1) {
            return [];
        }

        /** @var int|null $locationId */
        $locationId = Asset::HOOK_TO_LOCATION[$currentHook] ?? null;
        if (!$locationId) {
            return [];
        }

        $found = [];

        foreach ($this->assets->all() as $type => $assets) {
            foreach ($assets as $asset) {
                $handlerName = $asset->handler();
                $handler = $this->handlers[$handlerName] ?? null;
                if (!$handler) {
                    continue;
                }

                $location = $asset->location();
                if (($location & $locationId) !== $locationId) {
                    continue;
                }

                $found[] = $asset;
                if (!$process) {
                    continue;
                }

                $done = $asset->enqueue()
                    ? $handler->enqueue($asset)
                    : $handler->register($asset);
                if ($done && ($handler instanceof OutputFilterAwareAssetHandler)) {
                    $handler->filter($asset);
                }

                $this->processedAssets[$type . '_' . $handlerName] = $done;
            }
        }

        return $found;
    }

    protected function isAssetProcessed(Asset $asset): bool
    {
        return (bool) ($this->processedAssets[get_class($asset) . '_' . $asset->handle()] ?? false);
    }

    /**
     * @return void
     */
    private function ensureSetup(): void
    {
        if ($this->setupDone) {
            return;
        }

        $this->setupDone = true;

        $lastHook = $this->hookResolver->lastHook();

        /**
         * We should not setup if there's no hook or last hook already fired.
         *
         * @psalm-suppress PossiblyNullArgument
         */
        if (!$lastHook && did_action($lastHook) && !doing_action($lastHook)) {
            $this->assets = new AssetCollection();

            return;
        }

        $this->useDefaultHandlers();
        do_action(self::ACTION_SETUP, $this);
    }
}
