<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\Caching;


use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetManager;
use Inpsyde\Assets\BaseAsset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class IgnoreCacheHandler{
    public function execute(AssetManager $assetManager)
    {
        /** @var IgnorePluginCacheInterface[] $handlers */
        $handlers = [
            new IgnoreW3TotalCache(),
            new IgnoreSitegroundCache()
        ];

        $assets = $assetManager->assets();

        $assetHandles = [
            Script::class => [],
            Style::class => []
        ];

        foreach($assets as $assetKey => $assetType){
            foreach($assetType as $asset){
                $assetHandles[$assetKey][] = $asset->handle();
            }
        }

        foreach($handlers as $ignorePluginHandler){
            if($ignorePluginHandler->isInstalled()){
                $ignorePluginHandler->apply($assetHandles);
            }
        }
    }
}