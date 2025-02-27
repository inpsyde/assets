<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Exception\FileNotFoundException;

/**
 * @package Inpsyde\Assets\Loader
 */
class PhpFileLoader extends ArrayLoader
{
    /**
     * @param mixed $resource the path to your php-file.
     *
     * @return Asset[]
     *
     * phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     * @psalm-suppress UnresolvableInclude
     */
    public function load($resource): array
    {
        if (!is_string($resource) || !is_readable($resource)) {
            throw new FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    esc_html($resource)
                )
            );
        }

        $data = require $resource;

        return parent::load($data);
    }
}
