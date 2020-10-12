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

use Inpsyde\Assets\Exception\FileNotFoundException;

/**
 * @package Inpsyde\Assets\Loader
 */
class PhpFileLoader extends ArrayLoader
{
    /**
     * @param mixed $resource
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function load($resource): array
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        if (!is_readable($resource)) {
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
