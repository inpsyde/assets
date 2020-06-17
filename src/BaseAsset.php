<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

abstract class BaseAsset implements Asset
{

    /**
     * Set to "false" and the version will not automatically discovered.
     *
     * @see BaseAsset::disableAutodiscoverVersion()
     * @see BaseAsset::enableAutodiscoverVersion()
     *
     * @var bool
     */
    protected $autodiscoverVersion = true;

    /**
     * Default config values for an Asset.
     *
     * @var array
     */
    protected $config = [
        'url' => '',
        'filePath' => '',
        'handle' => '',
        'dependencies' => [],
        'location' => Asset::FRONTEND,
        'version' => null,
        'enqueue' => true,
        'filters' => [],
    ];

    public function __construct(
        string $handle,
        string $url,
        int $location = Asset::FRONTEND,
        array $config = []
    ) {

        $config['handle'] = $handle;
        $config['url'] = $url;
        $config['location'] = $location;

        $this->config = array_replace($this->config, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function url(): string
    {
        return (string) $this->config('url', '');
    }

    /**
     * {@inheritDoc}
     */
    public function handle(): string
    {
        return (string) $this->config('handle', '');
    }

    /**
     * {@inheritDoc}
     */
    public function filePath(): string
    {
        $filePath = (string) $this->config('filePath', '');

        if ($filePath !== '') {
            return $filePath;
        }

        $filePath = AssetPathResolver::resolve($this->url());
        // if replacement fails, don't set the url as path.
        if ($filePath === null || ! file_exists($filePath)) {
            return '';
        }

        $this->withFilePath($filePath);

        return $filePath;
    }

    /**
     * {@inheritDoc}
     *
     * @return Asset|Script|Style
     */
    public function withFilePath(string $filePath): Asset
    {
        $this->config['filePath'] = $filePath;

        return $this;
    }

    /**
     * Returns a version which will be automatically generated based on file time by default.
     *
     * @return string|null
     */
    public function version(): ?string
    {
        $version = $this->config('version', null);

        if ($version === null && $this->autodiscoverVersion) {
            $filePath = $this->filePath();
            $version = (string) filemtime($filePath);
            $this->withVersion($version);

            return $version;
        }

        return $version;
    }

    /**
     * {@inheritDoc}
     */
    public function withVersion(string $version): Asset
    {
        $this->config['version'] = $version;

        return $this;
    }

    /**
     * Enable automatic discovering of the version if no version is set.
     *
     * @return Script|Style
     */
    public function enableAutodiscoverVersion(): Asset
    {
        $this->autodiscoverVersion = true;

        return $this;
    }

    /**
     * Disable automatic discovering of the version if no version is set.
     *
     * @return Script|Style
     */
    public function disableAutodiscoverVersion(): Asset
    {
        $this->autodiscoverVersion = false;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function dependencies(): array
    {
        return array_unique($this->config('dependencies', []));
    }

    /**
     * {@inheritDoc}
     */
    public function withDependencies(string ...$dependencies): Asset
    {
        $this->config['dependencies'] = array_merge(
            $this->dependencies(),
            $dependencies
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function location(): int
    {
        return (int) $this->config('location', self::FRONTEND);
    }

    /**
     * @param int $location
     *
     * @return Script|Style
     */
    public function forLocation(int $location): Asset
    {
        $this->config['location'] = $location;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function filters(): array
    {
        return $this->config('filters', []);
    }

    /**
     * @param callable|OutputFilter\AssetOutputFilter ...$filters
     *
     * @return Style|Script
     *
     * // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function withFilters(...$filters): Asset
    {
        $this->config['filters'] = array_merge(
            $this->filters(),
            $filters
        );

        return $this;
    }

    /**
     * Shortcut to use the InlineFilter.
     * @return Style|Script
     */
    public function useInlineFilter(): Asset
    {
        $this->withFilters(InlineAssetOutputFilter::class);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(): bool
    {
        $enqueue = $this->config('enqueue', true);
        is_callable($enqueue) and $enqueue = $enqueue();

        return (bool) $enqueue;
    }

    /**
     * {@inheritDoc}
     *
     * // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function canEnqueue($enqueue): Asset
    {
        $this->config['enqueue'] = $enqueue;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function useHandler(string $handlerClass): Asset
    {
        $this->config['handler'] = $handlerClass;

        return $this;
    }

    public function handler(): string
    {
        return (string) $this->config('handler', $this->defaultHandler());
    }

    /**
     * @return string className of the default handler
     */
    abstract protected function defaultHandler(): string;

    /**
     * {@inheritDoc}
     */
    public function data(): array
    {
        return (array)$this->config('data', []);
    }

    /**
     * {@inheritDoc}
     *
     * @return Asset|Style|Script
     */
    public function withCondition(string $condition): Asset
    {
        $this->config['data']['conditional'] = $condition;

        return $this;
    }

    /**
     * Retrieve a value from a config with a fallback if not existing.
     *
     * @param string $key
     * @param null $default
     *
     * @return mixed|null
     *
     * // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * // phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
