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
        $config = self::migrateConfig($config);

        self::validateConfig($config);

        $location = $config['location'] ?? Asset::FRONTEND;
        $handle = $config['handle'];
        $url = $config['url'];
        $class = (string) $config['type'];

        $asset = new $class($handle, $url, $location, $config);

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
     * Migration of old config "type" => "location", "class" => "type" definition.
     *
     * @example [ 'type' => Asset::FRONTEND, 'class' => Script::class ]
     *          => [ 'location' => Asset::FRONTEND, 'type' => Script::class ]
     *
     * @since 1.1
     *
     * @param array $config
     *
     * @return array
     */
    private static function migrateConfig(array $config): array
    {
        // if old format "type" and "class" is set, migrate.
        if (isset($config['class'])) {
            do_action(
                'inpsyde.assets.debug',
                'The asset config-format with "type" and "class" is deprecated.',
                $config
            );

            $config['location'] = $config['type'] ?? Asset::FRONTEND;
            $config['type'] = $config['class'];

            unset($config['class']);
        }

        return $config;
    }

    /**
     * @param array $config
     *
     * @throws Exception\MissingArgumentException
     */
    private static function validateConfig(array $config)
    {
        $requiredFields = [
            'type',
            'url',
            'handle',
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
