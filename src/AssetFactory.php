<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

final class AssetFactory
{

    /**
     * @param array $config
     *
     * @return Asset
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
     */
    public static function create(array $config): Asset
    {
        self::validateConfig($config);

        $type = $config['type'];
        $handle = $config['handle'];
        $url = $config['url'];
        $class = (string) $config['class'];

        $asset = new $class($handle, $url, $type, $config);

        if (! $asset instanceof Asset) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The given class "%s" is not implementing %s',
                    $class,
                    Asset::class
                )
            );
        }

        return $asset;
    }

    /**
     * @param array $config
     *
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
     */
    private static function validateConfig(array $config)
    {
        $requiredFields = [
            'type',
            'url',
            'handle',
            'class',
        ];

        foreach ($requiredFields as $key) {
            if (! isset($config[$key])) {
                throw new Exception\MissingArgumentException(
                    sprintf(
                        'The given config <code>%s</code> is missing.',
                        $key
                    )
                );
            }
        }

        if (! isset(Asset::HOOKS[$config['type']])) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The given type "%s" is not allowed.',
                    $config['type']
                )
            );
        }
    }

    /**
     * @param string $file
     *
     * @return array
     * @throws Exception\FileNotFoundException
     */
    public static function createFromFile(string $file): array
    {
        if (! file_exists($file)) {
            throw new Exception\FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists.',
                    $file
                )
            );
        }

        $data = include_once $file;

        return self::createFromArray($data);
    }

    public static function createFromArray(array $data): array
    {
        return array_map(
            [
                __CLASS__,
                'create',
            ],
            $data
        );
    }
}
