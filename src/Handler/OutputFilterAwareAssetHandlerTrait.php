<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;

trait OutputFilterAwareAssetHandlerTrait
{
    /**
     * @var array<string, callable|class-string<AssetOutputFilter>>
     */
    protected $outputFilters = [];

    /**
     * @param string $name
     * @param callable $filter
     *
     * @return OutputFilterAwareAssetHandler
     */
    public function withOutputFilter(string $name, callable $filter): OutputFilterAwareAssetHandler
    {
        $this->outputFilters[$name] = $filter;

        return $this;
    }

    /**
     * @return array<string, callable|class-string<AssetOutputFilter>>
     */
    public function outputFilters(): array
    {
        return $this->outputFilters;
    }

    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function filter(Asset $asset): bool
    {
        $filters = $this->currentOutputFilters($asset);
        if (count($filters) === 0) {
            return false;
        }

        add_filter(
            $this->filterHook(),
            static function (string $html, string $handle) use ($filters, $asset): string {
                if ($handle !== $asset->handle()) {
                    return $html;
                }
                foreach ($filters as $filter) {
                    /** @psalm-suppress MixedFunctionCall */
                    $html = (string) $filter($html, $asset);
                }

                return $html;
            },
            10,
            2
        );

        return true;
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
