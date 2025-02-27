<?php

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

interface AssetOutputFilter
{
    /**
     * @param string $html
     * @param Asset $asset
     *
     * @return string $html
     */
    public function __invoke(string $html, Asset $asset): string;
}
