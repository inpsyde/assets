<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

/**
 * @phpstan-type Assets array<Style::class|Script::class|ScriptModule::class, array<string, Asset>>
 */
class AssetCollection
{
    /**
     * @var Assets
     */
    protected array $assets = [];

    public function add(Asset $asset): void
    {
        $type = get_class($asset);
        $handle = $asset->handle();
        $this->assets[$type][$handle] = $asset;
    }

    public function get(string $handle, string $type): ?Asset
    {
        $found = null;
        foreach ($this->assets as $assets) {
            foreach ($assets as $asset) {
                if ($asset->handle() !== $handle) {
                    continue;
                }
                if (is_a($asset, $type)) {
                    $found = $asset;
                    break 2;
                }
            }
        }

        return $found;
    }

    public function has(string $handle, string $type): bool
    {
        return $this->get($handle, $type) !== null;
    }

    /**
     * @return Assets
     */
    public function all(): array
    {
        return $this->assets;
    }
}
