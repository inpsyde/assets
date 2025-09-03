<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\FilterAwareAsset;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;

trait OutputFilterAwareAssetHandlerTrait
{
    /**
     * @var array<string, callable|class-string<AssetOutputFilter>>
     */
    protected array $outputFilters = [];

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
                    if (!is_callable($filter)) {
                        continue;
                    }
                    $html = (string) $filter($html, $asset);
                }

                return $html;
            },
            10,
            2
        );

        return true;
    }

    /**
     * @param Asset $asset
     *
     * @return array<class-string<AssetOutputFilter>|callable>
     */
    protected function currentOutputFilters(Asset $asset): array
    {
        $filters = [];
        $registeredFilters = $this->outputFilters();

        if (!$asset instanceof FilterAwareAsset) {
            return $filters;
        }

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
