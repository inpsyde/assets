<?php declare(strict_types=1);

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
        $scriptHandler = new ScriptHandler(wp_scripts());
        $styleHandler = new StyleHandler(wp_styles());

        $this->handlers = [
            Asset::TYPE_STYLE => $styleHandler,
            Asset::TYPE_ADMIN_STYLE => $styleHandler,
            Asset::TYPE_SCRIPT => $scriptHandler,
            Asset::TYPE_CUSTOMIZER_SCRIPT => $scriptHandler,
            Asset::TYPE_ADMIN_SCRIPT => $scriptHandler,
            Asset::TYPE_LOGIN_SCRIPT => $scriptHandler,
        ];

        return $this;
    }

    public function withHandler(string $assetType, AssetHandler $handler): AssetManager
    {
        $this->handlers[$assetType] = $handler;

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
                $this->assets["{$asset->type()}_{$asset->handle()}"] = $asset;
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

        $currentHook = $this->currentHook();
        if ($currentHook === '') {
            return false;
        }

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

        return true;
    }

    private function processAssets(array $assets)
    {
        foreach ($assets as $asset) {
            $handler = $this->handlers[$asset->type()];

            if (! $asset->enqueue()) {
                $handler->register($asset);
                continue;
            }

            $handler->enqueue($asset);

            if ($handler instanceof OutputFilterAwareAssetHandler) {
                $handler->filter($asset);
            }
        }
    }

    private function currentAssets(string $currentHook): array
    {
        return array_filter(
            $this->assets,
            function (Asset $asset) use ($currentHook): bool {
                $type = $asset->type();

                if (! isset(Asset::HOOKS[$type])) {
                    return false;
                }
                if (Asset::HOOKS[$type] !== $currentHook) {
                    return false;
                }
                if (! isset($this->handlers[$type])) {
                    return false;
                }

                return true;
            }
        );
    }

    private function currentHook(): string
    {
        global $pagenow;
        if ($pagenow === 'wp-login.php') {
            return empty($GLOBALS['interim_login'])
                ? 'login_enqueue_scripts'
                : '';
        }

        if (! is_admin()) {
            return 'wp_enqueue_scripts';
        }

        if (is_customize_preview()) {
            return 'customize_controls_enqueue_scripts';
        }

        return wp_doing_ajax()
            ? ''
            : 'admin_enqueue_scripts';
    }
}
