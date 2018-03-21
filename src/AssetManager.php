<?php declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;

final class AssetManager
{

    const ACTION_SETUP = 'inpsyde.assets.setup';
    protected $filters = [];
    /**
     * @var array
     */
    private $assets = [];
    private $handlers = [];

    public function useDefaultHandlers(): AssetManager
    {
        $this->handlers = [
            Asset::TYPE_SCRIPT => new ScriptHandler($GLOBALS['wp_scripts']),
            Asset::TYPE_STYLE => new StyleHandler($GLOBALS['wp_styles']),
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

    public function useDefaultOutputFilters(): AssetManager
    {
        $this->filters = [
            AsyncStyleOutputFilter::class => new AsyncScriptOutputFilter(),
            AsyncScriptOutputFilter::class => new AsyncStyleOutputFilter(),
            DeferScriptOutputFilter::class => new DeferScriptOutputFilter(),
        ];

        return $this;
    }

    public function withOutputFilter(string $name, AssetOutputFilter $filter): AssetManager
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * @return AssetOutputFilter[]
     */
    public function outputFilters(): array
    {
        return $this->filters;
    }

    public function register(Asset $asset): AssetManager
    {
        $this->assets["{$asset->type()}_{$asset->handle()}"] = $asset;

        return $this;
    }

    public function registerMultiple(array $assets): AssetManager
    {
        array_walk(
            $assets,
            [
                $this,
                'register',
            ]
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
     * @param int $priority
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return bool
     */
    public function setup(int $priority = 10): bool
    {
        if (did_action(self::ACTION_SETUP)) {
            return false;
        }

        do_action(self::ACTION_SETUP);

        foreach ($this->assets as $asset) {
            $type = $asset->type();
            if (! isset($this->handlers[$type])) {
                continue;
            }
            $handler = $this->handlers[$type];
            $handler->handle($asset);
            $this->processFilters($asset, $handler->filterHook());
        }

        return true;
    }

    /**
     * @param Asset $asset
     * @param string $hook
     *
     * @return bool true when at least 1 filter is applied, otherwise false.
     */
    protected function processFilters(Asset $asset, string $hook): bool
    {
        $filters = array_filter(
            $asset->filters(),
            // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
            function ($filter): bool {

                return is_callable($filter);
            }
        );

        if (count($filters) < 1) {
            return false;
        }

        add_filter(
            $hook,
            function (string $html) use ($filters, $asset): string {
                foreach ($filters as $filter) {
                    $html = (string)$filter($html, $asset);
                }

                return $html;
            }
        );

        return true;
    }
}
