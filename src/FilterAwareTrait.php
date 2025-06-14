<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

trait FilterAwareTrait
{
    /**
     * @var callable[]|AssetOutputFilter[]|class-string<AssetOutputFilter>[]
     */
    protected array $filters = [];

    /**
     * Additional attributes to "link"- or "script"-tag.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @return callable[]|AssetOutputFilter[]|class-string<AssetOutputFilter>[]
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @param callable|class-string<AssetOutputFilter> ...$filters
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     */
    public function withFilters(...$filters): Asset
    {
        // phpcs:enable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType

        $this->filters = array_merge($this->filters, $filters);

        return $this;
    }

    /**
     * Shortcut to use the InlineFilter.
     *
     * @return static
     */
    public function useInlineFilter(): Asset
    {
        $this->withFilters(InlineAssetOutputFilter::class);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Allows you to set additional attributes to your "link"- or "script"-tag.
     * Existing attributes like "src" or "id" will not be overwrite.
     *
     * @param array<string, mixed> $attributes
     *
     * @return static
     */
    public function withAttributes(array $attributes): Asset
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        $this->withFilters(AttributesOutputFilter::class);

        return $this;
    }
}
