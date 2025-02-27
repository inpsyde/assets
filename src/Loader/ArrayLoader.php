<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\BaseAsset;
use Inpsyde\Assets\ConfigureAutodiscoverVersionTrait;

/**
 * @package Inpsyde\Assets\Loader
 */
class ArrayLoader implements LoaderInterface
{
    use ConfigureAutodiscoverVersionTrait;

    /**
     * @param mixed $resource
     *
     * @return Asset[]
     *
     * phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     * @psalm-suppress MixedArgument
     */
    public function load($resource): array
    {
        $assets = array_map(
            [AssetFactory::class, 'create'],
            (array) $resource
        );

        return array_map(
            function (Asset $asset): Asset {
                if ($asset instanceof BaseAsset) {
                    $this->autodiscoverVersion
                        ? $asset->enableAutodiscoverVersion()
                        : $asset->disableAutodiscoverVersion();
                }
                return $asset;
            },
            $assets
        );
    }
}
