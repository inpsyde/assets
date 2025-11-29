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

    /**
     * @param Asset $asset
     *
     * @return void
     */
    public function add(Asset $asset): void
    {
        $type = get_class($asset);
        $handle = $asset->handle();
        $this->assets[$type][$handle] = $asset;
    }

    /**
     * @param string $handle
     * @param class-string $type
     *
     * @return Asset|null
     */
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

    /**
     * @param string $handle
     *
     * @return Asset|null
     *
     * phpcs:disable Syde.Classes.DisallowGetterSetter.GetterFound
     */
    public function getFirst(string $handle): ?Asset
    {
        $found = null;
        foreach ($this->assets as $assets) {
            foreach ($assets as $asset) {
                if ($asset->handle() === $handle) {
                    $found = $asset;
                    break 2;
                }
            }
        }

        return $found;
    }

    /**
     * @param string $handle
     * @param class-string $type
     *
     * @return bool
     */
    public function has(string $handle, string $type): bool
    {
        return $this->get($handle, $type) !== null;
    }

    /**
     * @return Assets
     */
    public function all(): array
    {
        $sorted = [];
        foreach ($this->assets as $type => $assets) {
            uasort($assets, static function (Asset $assetA, Asset $assetB): int {
                $priorityA = $assetA instanceof PrioritizedAsset ? $assetA->priority() : 10;
                $priorityB = $assetB instanceof PrioritizedAsset ? $assetB->priority() : 10;
                return $priorityA <=> $priorityB;
            });
            $sorted[$type] = $assets;
        }
        return $sorted;
    }
}
