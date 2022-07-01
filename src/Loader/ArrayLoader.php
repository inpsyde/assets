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

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Asset;
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
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
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
