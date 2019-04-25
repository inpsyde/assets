<?php declare(strict_types=1); # -*- coding: utf-8 -*-
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

    private $bootstrapped = false;

    private $assets = [];

    private $handlers = [];

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
                $this->assets[] = $asset;
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
     * @wp-hook wp_enqueue_scripts
     *
     * @return bool
     */
    public function setup(): bool
    {
        if ($this->bootstrapped) {
            return false;
        }
        $this->bootstrapped = true;

        $currentHooks = $this->currentHooks();
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

    public function currentAssets(string $currentHook): array
    {
        if (! isset(Asset::HOOK_TO_LOCATION[$currentHook])) {
            return [];
        }

        return array_filter(
            $this->assets,
            function (Asset $asset) use ($currentHook): bool {
                $handler = $asset->handler();
                if (! isset($this->handlers[$handler])) {
                    return false;
                }

                $location = $asset->location();
                if ($location & Asset::HOOK_TO_LOCATION[$currentHook]) {
                    return true;
                }

                return false;
            }
        );
    }

    // phpcs:ignore Generic.Metrics.NestingLevel.TooHigh
    private function currentHooks(): array
    {
        $pageNow = $GLOBALS['pagenow'] ?? '';
        $pageNow = basename($pageNow);

        $isCore = defined('ABSPATH');
        $isAjax = $isCore
            ? wp_doing_ajax()
            : false;
        $isAdmin = $isCore
            ? is_admin() && ! $isAjax
            : false;
        $isCron = $isCore
            ? wp_doing_cron()
            : false;
        $isLogin = ($pageNow === 'wp-login.php');
        $isPostEdit = ($pageNow === 'post.php');
        $isCli = defined('WP_CLI');
        $isFront = ! $isAdmin && ! $isAjax && ! $isCron && ! $isLogin && ! $isCli;
        $isCustomizer = is_customize_preview();

        $hooks = [];

        if ($isAjax) {
            return [];
        }

        if ($isLogin) {
            $hooks[] = 'login_enqueue_scripts';
        }

        if ($isPostEdit) {
            $hooks[] = 'enqueue_block_editor_assets';
        }

        if ($isFront) {
            $hooks[] = 'wp_enqueue_scripts';
        }

        if ($isCustomizer) {
            $hooks[] = 'customize_controls_enqueue_scripts';
        }

        if ($isAdmin) {
            $hooks[] = 'admin_enqueue_scripts';
        }

        return $hooks;
    }
}
