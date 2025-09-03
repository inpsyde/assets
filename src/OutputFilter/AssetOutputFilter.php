<?php

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\FilterAwareAsset;

interface AssetOutputFilter
{
    /**
     * @param string $html
     * @param FilterAwareAsset $asset
     *
     * @return string $html
     */
    public function __invoke(string $html, FilterAwareAsset $asset): string;
}
