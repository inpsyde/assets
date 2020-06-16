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

use Inpsyde\Assets\Exception\FileNotFoundException;

/**
 * @package Inpsyde\Assets\Loader
 */
class PhpFileLoader extends ArrayLoader
{

    /**
     * {@inheritDoc}
     *
     * @throws FileNotFoundException
     */
    public function load($resource): array
    {
        if (! is_readable($resource)) {
            throw new FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    $resource
                )
            );
        }

        $data = require $resource;

        return parent::load($data);
    }
}
