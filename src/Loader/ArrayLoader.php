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

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\AssetFactory;

/**
 * @package Inpsyde\Assets\Loader
 */
class ArrayLoader implements LoaderInterface
{

    /**
     * {@inheritDoc}
     */
    public function load($data): array
    {
        $assets = array_map(
            [AssetFactory::class, 'create'],
            (array) $data
        );

        return $assets;
    }
}
