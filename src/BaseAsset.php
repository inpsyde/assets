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

use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

abstract class BaseAsset implements Asset
{
    use ConfigureAutodiscoverVersionTrait;

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

    /**
     * @param string $handle
     * @param string $url
     * @param int $location
     * @param array $config
     */
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
     * @return string
     */
    public function url(): string
    {
        return (string) $this->config('url', '');
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        return (string) $this->config('handle', '');
    }

    /**
     * @return string
     */
    public function filePath(): string
    {
        $filePath = (string) $this->config('filePath', '');

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
     * @return static
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

        return $version === null ? null : (string) $version;
    }

    /**
     * @param string $version
     * @return static
     */
    public function withVersion(string $version): Asset
    {
        $this->config['version'] = $version;

        return $this;
    }

    /**
     * @return array
     */
    public function dependencies(): array
    {
        return array_unique($this->config('dependencies', []));
    }

    /**
     * @param string ...$dependencies
     * @return static
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
     * @return int
     */
    public function location(): int
    {
        return (int) $this->config('location', self::FRONTEND);
    }

    /**
     * @param int $location
     * @return static
     */
    public function forLocation(int $location): Asset
    {
        $this->config['location'] = $location;

        return $this;
    }

    /**
     * @return array|null
     */
    public function filters(): array
    {
        return $this->config('filters', []);
    }

    /**
     * @param callable|class-string<OutputFilter\AssetOutputFilter> ...$filters
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function withFilters(...$filters): Asset
    {
        // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $this->config['filters'] = array_merge($this->filters(), $filters);

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
        $enqueue = $this->config('enqueue', true);
        is_callable($enqueue) and $enqueue = $enqueue();

        return (bool) $enqueue;
    }

    /**
     * @param bool|callable $enqueue
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function canEnqueue($enqueue): Asset
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $this->config['enqueue'] = $enqueue;

        return $this;
    }

    /**
     * @param string $handlerClass
     * @return static
     */
    public function useHandler(string $handlerClass): Asset
    {
        $this->config['handler'] = $handlerClass;

        return $this;
    }

    /**
     * @return string
     */
    public function handler(): string
    {
        return (string) $this->config('handler', $this->defaultHandler());
    }

    /**
     * @return string className of the default handler
     */
    abstract protected function defaultHandler(): string;

    /**
     * @return array
     */
    public function data(): array
    {
        return (array)$this->config('data', []);
    }

    /**
     * @param string $condition
     * @return static
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
     * @return mixed|null
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function config(string $key, $default = null)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return $this->config[$key] ?? $default;
    }
}
