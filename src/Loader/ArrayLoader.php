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
     * @param mixed $data
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function load($data): array
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $assets = array_map(
            [AssetFactory::class, 'create'],
            (array) $data
        );

        if (!$this->autodiscoverVersion) {
            $assets = array_map(
                static function (BaseAsset $asset): Asset {
                    return $asset->disableAutodiscoverVersion();
                },
                $assets
            );
        }

        return $assets;
    }
}
