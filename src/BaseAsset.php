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

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Util\AssetPathResolver;
use Inpsyde\Assets\OutputFilter\AssetOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

/**
 * phpcs:disable Inpsyde.CodeQuality.PropertyPerClassLimit.TooManyProperties
 */
abstract class BaseAsset implements Asset
{
    use ConfigureAutodiscoverVersionTrait;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * Full filePath to an Asset which can
     * be used to auto-discover version or
     * load Asset content inline.
     *
     * @var string
     */
    protected $filePath = '';

    /**
     * @var string
     */
    protected $handle = '';

    /**
     * Dependencies to other Asset handles.
     *
     * @var string[]
     */
    protected $dependencies = [];

    /**
     * Location where the Asset will be enqueued.
     *
     * @var int
     */
    protected $location = self::FRONTEND;

    /**
     * Version can be auto-discovered if null.
     *
     * @see BaseAsset::enableAutodiscoverVersion().
     *
     * @var null|string
     */
    protected $version = null;

    /**
     * @var bool|callable(): bool
     */
    protected $enqueue = true;

    /**
     * @var callable[]|AssetOutputFilter[]|class-string<AssetOutputFilter>[]
     */
    protected $filters = [];

    /**
     * @var class-string<AssetHandler>|null
     */
    protected $handler = null;

    /**
     * Data which will be added via ...
     *      - WP_Script::add_data()
     *      - WP_Style::add_data()
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Additional attributes to "link"- or "script"-tag.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [];

    /**
     * @param string $handle
     * @param string $url
     * @param int $location
     */
    public function __construct(
        string $handle,
        string $url,
        int $location = Asset::FRONTEND | Asset::ACTIVATE
    ) {

        $this->handle = $handle;
        $this->url = $url;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function filePath(): string
    {
        $filePath = $this->filePath;

        if ($filePath !== '') {
            return $filePath;
        }

        try {
            $filePath = AssetPathResolver::resolve($this->url());
        } catch (\Throwable $throwable) {
            $filePath = null;
        }

        // if replacement fails, don't set the url as path.
        if ($filePath === null || !file_exists($filePath)) {
            return '';
        }

        $this->withFilePath($filePath);

        return $filePath;
    }

    /**
     * @param string $filePath
     *
     * @return static
     */
    public function withFilePath(string $filePath): Asset
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Returns a version which will be automatically generated based on file time by default.
     *
     * @return string|null
     */
    public function version(): ?string
    {
        $version = $this->version;

        if ($version === null && $this->autodiscoverVersion) {
            $filePath = $this->filePath();
            $version = (string) filemtime($filePath);
            $this->withVersion($version);

            return $version;
        }

        return $version === null
            ? null
            : (string) $version;
    }

    /**
     * @param string $version
     *
     * @return static
     */
    public function withVersion(string $version): Asset
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return array_values(array_unique($this->dependencies));
    }

    /**
     * @param string ...$dependencies
     *
     * @return static
     */
    public function withDependencies(string ...$dependencies): Asset
    {
        $this->dependencies = array_merge(
            $this->dependencies,
            $dependencies
        );

        return $this;
    }

    /**
     * @return int
     */
    public function location(): int
    {
        return (int) $this->location;
    }

    /**
     * @param int $location
     *
     * @return static
     */
    public function forLocation(int $location): Asset
    {
        $this->location = $location;

        return $this;
    }

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
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function withFilters(...$filters): Asset
    {
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
     * @return bool
     */
    public function enqueue(): bool
    {
        $enqueue = $this->enqueue;
        is_callable($enqueue) and $enqueue = $enqueue();

        return (bool) $enqueue;
    }

    /**
     * @param bool|callable(): bool $enqueue
     *
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function canEnqueue($enqueue): Asset
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $this->enqueue = $enqueue;

        return $this;
    }

    /**
     * @param class-string<AssetHandler> $handler
     *
     * @return static
     */
    public function useHandler(string $handler): Asset
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return class-string<AssetHandler>
     */
    public function handler(): string
    {
        if (!$this->handler) {
            $this->handler = $this->defaultHandler();
        }

        return $this->handler;
    }

    /**
     * @return class-string<AssetHandler> className of the default handler
     */
    abstract protected function defaultHandler(): string;

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Allows to set additional data via WP_Script::add_data() or WP_Style::add_data().
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function withData(array $data): Asset
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Shortcut for Asset::withData(['conditional' => $condition]);
     *
     * @param string $condition
     *
     * @return static
     */
    public function withCondition(string $condition): Asset
    {
        $this->withData(['conditional' => $condition]);

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
