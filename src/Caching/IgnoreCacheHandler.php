<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Caching;

use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class IgnoreCacheHandler
{
    public function execute(AssetManager $assetManager): void
    {
        /** @var IgnorePluginCacheInterface[] $handlers */
        $handlers = [
            new IgnoreW3TotalCache(),
            new IgnoreSitegroundCache(),
        ];

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

        foreach ($handlers as $ignorePluginHandler) {
            if ($ignorePluginHandler->isInstalled()) {
                $ignorePluginHandler->apply($assetHandles);
            }
        }
    }
}
