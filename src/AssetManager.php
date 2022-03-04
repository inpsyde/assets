<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\Util\AssetHookResolver;
use Inpsyde\Assets\Asset;

final class AssetManager
{
    public const ACTION_SETUP = 'inpsyde.assets.setup';

    /**
     * Contains the state of the AssetManager, where keys are hook names that are already added
     * to avoid add them more than once.
     *
     * @var array<string, bool>
     */
    private $hooksAdded = [];

    /**
     * @var \SplObjectStorage<Asset, array{string, string}>
     */
    private $assets;

    /**
     * @var array<AssetHandler>
     */
    private $handlers = [];

    /**
     * @var AssetHookResolver
     */
    private $hookResolver;

    /**
     * @var bool
     */
    private $setupDone = false;

    /**
     * @param AssetHookResolver|null $hookResolver
     */
    public function __construct(AssetHookResolver $hookResolver = null)
    {
        $this->hookResolver = $hookResolver ?? new AssetHookResolver();
        $this->assets = new \SplObjectStorage();
    }

    /**
     * @return static
     */
    public function useDefaultHandlers(): AssetManager
    {
        empty($this->handlers[StyleHandler::class])
        and $this->handlers[StyleHandler::class] = new StyleHandler(wp_styles());

        empty($this->handlers[ScriptHandler::class])
        and $this->handlers[ScriptHandler::class] = new ScriptHandler(wp_scripts());

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
            $handle = $asset->handle();
            if ($handle) {
                $this->assets->attach($asset, [$handle, get_class($asset)]);
            }
        }

        return $this;
    }

    /**
     * @return array<string, array<string, Asset>>
     */
    public function assets(): array
    {
        $this->ensureSetup();

        $found = [];
        $this->assets->rewind();
        while ($this->assets->valid()) {
            $asset = $this->assets->current();
            [$handle, $class] = $this->assets->getInfo();
            isset($found[$class]) or $found[$class] = [];
            $found[$class][$handle] = $asset;

            $this->assets->next();
        }

        return $found;
    }

    /**
     * Retrieve an `Asset` instance by a given asset handle and type (class).
     *
     * If the handle is unique by type, type can be omitted.
     * It is possible to use a parent class as type.
     * E.g an asset of a type `MyScript` that extends `Script` can be also retrieved passing
     * either `MyScript or `Script` as type.
     *
     * @param string $handle
     * @param class-string|null $type
     *
     * @return Asset|null
     */
    public function asset(string $handle, ?string $type = null): ?Asset
    {
        $this->ensureSetup();

        /** @var Asset|null $found */
        $found = null;
        $this->assets->rewind();

        while ($this->assets->valid()) {
            $asset = $this->assets->current();
            $this->assets->next();

            if (($asset->handle() !== $handle) || ($type && !is_a($asset, $type))) {
                continue;
            }

            if ($found) {
                // only one asset can match
                return null;
            }

            $found = $asset;
        }

        return $found;
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
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     */
    private function loopCurrentHookAssets(string $currentHook, bool $process): array
    {
        $this->ensureSetup();
        if (!$this->assets->count()) {
            return [];
        }

        /** @var int|null $locationId */
        $locationId = Asset::HOOK_TO_LOCATION[$currentHook] ?? null;
        if (!$locationId) {
            return [];
        }

        $found = [];

        $this->assets->rewind();
        while ($this->assets->valid()) {
            $asset = $this->assets->current();
            $this->assets->next();

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
        }

        return $found;
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
            $this->assets = new \SplObjectStorage();

            return;
        }

        $this->useDefaultHandlers();
        do_action(self::ACTION_SETUP, $this);
    }
}
