<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

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
     * @return array
     */
    public function outputFilters(): array;
}
