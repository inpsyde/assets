<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Caching;

use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class IgnoreCacheHandler
{
    public function run(AssetManager $assetManager): bool
    {
        /** @var IgnorePluginCacheInterface[] $handlers */
        $handlers = [
            new IgnoreW3TotalCache(),
            new IgnoreSitegroundCache(),
        ];

        $assetHandles = $this->extractHandles($assetManager);

        if (
            count($assetHandles[Script::class]) === 0 &&
            count($assetHandles[Style::class]) === 0
        ) {
            return false;
        }

        foreach ($handlers as $ignorePluginHandler) {
            if ($ignorePluginHandler->isInstalled()) {
                $ignorePluginHandler->apply($assetHandles);
            }
        }
        return true;
    }

    protected function extractHandles(AssetManager $assetManager): array
    {
        $assets = $assetManager->assets();
        $assetHandles = [
            Script::class => [],
            Style::class => [],
        ];

        foreach ($assets as $assetKey => $assetType) {
            foreach ($assetType as $asset) {
                $assetHandles[$assetKey][] = $asset->handle();
            }
        }
        return $assetHandles;
    }
}
