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
        'version' => '',
        'enqueue' => true,
        'filters' => [],
        'data' => [],
    ];

    public function url(): string
    {
        return (string) $this->config['url'];
    }

    public function handle(): string
    {
        return (string) $this->config['handle'];
    }

    public function dependencies(): array
    {
        return (array) $this->config['dependencies'];
    }

    public function version(): string
    {
        return (string) $this->config['version'];
    }

    public function location(): int
    {
        return (int) ($this->config['location'] ?? self::FRONTEND);
    }

    public function filters(): array
    {
        return $this->config['filters'];
    }

    public function data(): array
    {
        $data = $this->config['data'];
        is_callable($data) and $data = $data();

        return (array) $data;
    }

    public function enqueue(): bool
    {
        $enqueue = $this->config['enqueue'];
        is_callable($enqueue) and $enqueue = $enqueue();

        return (bool) $enqueue;
    }
}
