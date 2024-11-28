<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Caching;

interface IgnorePluginCacheInterface
{
    public function isInstalled(): bool;
    public function apply(array $handles): void;
}
