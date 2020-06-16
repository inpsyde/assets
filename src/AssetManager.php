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

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\OutputFilterAwareAssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Handler\StyleHandler;

final class AssetManager
{

    const ACTION_SETUP = 'inpsyde.assets.setup';

    /**
     * Contains the state of the AssetManager to avoid booting the class twice.
     *
     * @var bool
     */
    private $bootstrapped = false;

    /**
     * @var Asset[]
     */
    private $assets = [];

    /**
     * @var AssetHandler[]
     */
    private $handlers = [];

    /**
     * @var AssetHookResolver
     */
    private $hookResolver;

    /**
     * AssetManager constructor.
     *
     * @param AssetHookResolver|null $hookResolver
     */
    public function __construct(AssetHookResolver $hookResolver = null)
    {
        $this->hookResolver = $hookResolver ?? new AssetHookResolver();
    }

    public function useDefaultHandlers(): AssetManager
    {
        $this->handlers = [
            StyleHandler::class => new StyleHandler(wp_styles()),
            ScriptHandler::class => new ScriptHandler(wp_scripts()),
        ];

        return $this;
    }

    public function withHandler(string $name, AssetHandler $handler): AssetManager
    {
        $this->handlers[$name] = $handler;

        return $this;
    }

    /**
     * @return AssetHandler[]
     */
    public function handlers(): array
    {
        return $this->handlers;
    }

    public function register(Asset ...$assets): AssetManager
    {
        array_walk(
            $assets,
            function (Asset $asset) {
                $class = get_class($asset);
                $this->assets[$class][$asset->handle()] = $asset;
            }
        );

        return $this;
    }

    /**
     * @return Asset[]
     */
    public function assets(): array
    {
        return $this->assets;
    }

    /**
     * Retrieve an Asset instance by a given handle and type of Asset.
     *
     * @param string $handle
     * @param string $type
     *
     * @return Asset|null
     */
    public function asset(string $handle, string $type): ?Asset
    {
        $asset = $this->assets[$type][$handle] ?? null;

        return $asset;
    }

    /**
     * @wp-hook wp_enqueue_scripts
     *
     * @return bool
     */
    // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh
    public function setup(): bool
    {
        if ($this->bootstrapped) {
            return false;
        }
        $this->bootstrapped = true;

        $currentHooks = $this->hookResolver->resolve();
        if (count($currentHooks) < 1) {
            return false;
        }

        foreach ($currentHooks as $currentHook) {
            add_action(
                $currentHook,
                function () use ($currentHook) {
                    if (! did_action(self::ACTION_SETUP)) {
                        $this->useDefaultHandlers();
                        do_action(self::ACTION_SETUP, $this);
                    }
                    $this->processAssets($this->currentAssets($currentHook));
                }
            );
        }

        return true;
    }

    /**
     * @param array $assets
     */
    private function processAssets(array $assets)
    {
        foreach ($assets as $asset) {
            $handler = $this->handlers[$asset->handler()];

            (! $asset->enqueue())
                ? $handler->register($asset)
                : $handler->enqueue($asset);

            if ($handler instanceof OutputFilterAwareAssetHandler) {
                $handler->filter($asset);
            }
        }
    }

    /**
     * Returning all matching assets to given hook.
     *
     * @param string $currentHook
     *
     * @return Asset[]
     */
    // phpcs:disable Generic.Metrics.NestingLevel.TooHigh
    public function currentAssets(string $currentHook): array
    {
        if (! isset(Asset::HOOK_TO_LOCATION[$currentHook])) {
            return [];
        }

        $currentAssets = [];
        foreach ($this->assets as $type => $assets) {
            /** @var Asset $asset */
            foreach ($assets as $handle => $asset) {
                $handler = $asset->handler();
                if (! isset($this->handlers[$handler])) {
                    continue;
                }

                $location = $asset->location();
                if ($location & Asset::HOOK_TO_LOCATION[$currentHook]) {
                    $currentAssets[] = $asset;
                }
            }
        }

        return $currentAssets;
    }
}
