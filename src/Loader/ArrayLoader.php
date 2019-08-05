<?php declare(strict_types=1); # -*- coding: utf-8 -*-

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
