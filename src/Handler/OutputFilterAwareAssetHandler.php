<?php

declare(strict_types=1);

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
