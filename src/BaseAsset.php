<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

abstract class BaseAsset implements Asset
{

    protected $config = [
        'url' => '',
        'handle' => '',
        'dependencies' => [],
        'location' => Asset::FRONTEND,
        'version' => '',
        'enqueue' => true,
        'filters' => [],
    ];

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
    public function version(): string
    {
        return (string) $this->config('version', '');
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
     * {@inheritDoc}
     */
    public function dependencies(): array
    {
        return array_unique($this->config('dependencies', []));
    }

    /**
     * @param string ...$dependencies
     *
     * @return Script|Style
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
        return $this->config('data', []);
    }

    /**
     * {@inheritDoc}
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
