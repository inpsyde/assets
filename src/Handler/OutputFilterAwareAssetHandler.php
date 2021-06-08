<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;

interface OutputFilterAwareAssetHandler
{
    /**
     * @param Asset $asset
     *
     * @return bool true when at least 1 filter is applied, otherwise false.
     */
    public function filter(Asset $asset): bool;

    /**
     * Register new outputFilters to the Handler.
     *
     * @param string $name
     * @param callable $filter
     *
     * @return OutputFilterAwareAssetHandler
     */
    public function withOutputFilter(string $name, callable $filter): OutputFilterAwareAssetHandler;

    /**
     * Returns all registered outputFilters.
     *
     * @return array<string, callable|class-string<AssetOutputFilter>>
     */
    public function outputFilters(): array;
}
