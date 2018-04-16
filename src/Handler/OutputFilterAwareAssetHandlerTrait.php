<?php declare(strict_types=1); # -*- coding: utf-8 -*-
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

trait OutputFilterAwareAssetHandlerTrait
{

    protected $outputFilters = [];

    public function withOutputFilter(string $name, callable $filter): OutputFilterAwareAssetHandler
    {
        $this->outputFilters[$name] = $filter;

        return $this;
    }

    public function outputFilters(): array
    {
        return $this->outputFilters;
    }

    public function filter(Asset $asset): bool
    {
        $filters = $this->currentOutputFilters($asset);
        if (count($filters) < 1) {
            return false;
        }

        add_filter(
            $this->filterHook(),
            function (string $html, string $handle) use ($filters, $asset): string {
                if ($handle !== $asset->handle()) {
                    return $html;
                }
                foreach ($filters as $filter) {
                    $html = (string) $filter($html, $asset);
                }

                return $html;
            },
            10,
            2
        );

        return count($filters) > 0;
    }

    protected function currentOutputFilters(Asset $asset): array
    {
        $filters = [];
        $registeredFilters = $this->outputFilters();
        foreach ($asset->filters() as $filter) {
            if (is_callable($filter)) {
                $filters[] = $filter;
                continue;
            }
            if (isset($registeredFilters[$filter])) {
                $filters[] = $registeredFilters[$filter];
                continue;
            }
        }

        return $filters;
    }

    /**
     * Defines the name of hook to filter the specific asset.
     *
     * @return string
     */
    abstract public function filterHook(): string;
}
