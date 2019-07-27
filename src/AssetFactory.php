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
        if (! is_readable($file)) {
            throw new Exception\FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    $file
                )
            );
        }

        $data = require_once($file);

        return self::createFromArray($data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
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

    /**
     * @param string $manifestFile
     * @param string $assetDirUrl
     *
     * @return array
     * @throws Exception\FileNotFoundException
     */
    public static function createFromManifest(string $manifestFile, string $assetDirUrl = ''): array
    {
        if (! is_readable($manifestFile)) {
            throw new Exception\FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    $manifestFile
                )
            );
        }

        $assetDirUrl = trailingslashit($assetDirUrl);
        $manifestDir = trailingslashit(dirname($manifestFile));
        $content = @file_get_contents($manifestFile)
            ?: '';
        $data = (array) @json_decode($content, true);

        $assets = [];
        foreach ($data as $handle => $file) {
            $sanitizedFile = self::sanitizeFile($file);

            [
                'extension' => $extension,
                'filename' => $filename,
            ] = pathinfo($sanitizedFile);
            // It can be possible, that the "handle"-key is a filepath.
            $handle = pathinfo($handle, PATHINFO_FILENAME);

            $class = self::resolveClassByExtension($extension);
            if ($class === '') {
                continue;
            }

            $fileUrl = ($assetDirUrl === '')
                ? $file
                : $assetDirUrl.$sanitizedFile;
            $filePath = $manifestDir.$sanitizedFile;

            $location = self::resolveLocation($filename);
            /** @var Asset $asset */
            $asset = new $class($handle, $fileUrl, $location);
            $asset->withFilePath($filePath);

            if ($extension === 'js') {
                $deps = self::resolveDependencies($filePath);
                /** @var Script $asset */
                $asset->withDependencies(...$deps);
            }

            $assets[] = $asset;
        }

        return $assets;
    }

    /**
     * The "file"-value in manifest.json can contain ...
     *      - URL
     *      - Path to current folder
     *      - Absolute path
     *
     * @param string $file
     *
     * @return string
     */
    private static function sanitizeFile(string $file): string
    {
        // Check, if the given "file"-value is an URL
        $parsedUrl = parse_url($file);
        $sanitizedFile = $parsedUrl['path'] ?? $file;
        // the "file"-value can contain "./file.css" or "/file.css".
        $sanitizedFile = ltrim($sanitizedFile, '.');
        $sanitizedFile = ltrim($sanitizedFile, '/');

        return $sanitizedFile;
    }

    /**
     * Resolving dependencies for JS files by searching for a {file}.deps.json file which contains
     * an array of dependencies.
     *
     * // phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
     *
     * @link https://github.com/WordPress/gutenberg/tree/master/packages/dependency-extraction-webpack-plugin
     *
     * @param string $filePath
     *
     * @return array
     */
    private static function resolveDependencies(string $filePath): array
    {
        $depsFile = str_replace('.js', '.deps.json', $filePath);
        if (! file_exists($depsFile)) {
            return [];
        }

        $data = @json_decode(@file_get_contents($depsFile));

        return (array) $data;
    }

    private static function resolveClassByExtension(string $extension): string
    {
        $extensionsToClass = [
            'css' => Style::class,
            'js' => Script::class,
        ];

        return $extensionsToClass[$extension] ?? '';
    }

    /**
     * Internal function to resolve a location for a given fileName.
     *
     * @param string $fileName
     *
     * @return int
     * @example     foo-customizer.css  -> Asset::CUSTOMIZER
     * @example     foo-block.css       -> Asset::BLOCK_EDITOR_ASSETS
     * @example     foo-login.css       -> Asset::LOGIN
     *
     * @example     foo.css             -> Asset::FRONTEND
     * @example     foo-backend.css     -> Asset::BACKEND
     */
    private static function resolveLocation(string $fileName): int
    {
        if (stristr($fileName, '-backend')) {
            return Asset::BACKEND;
        }

        if (stristr($fileName, '-block')) {
            return Asset::BLOCK_EDITOR_ASSETS;
        }

        if (stristr($fileName, '-login')) {
            return Asset::LOGIN;
        }

        if (stristr($fileName, '-customizer')) {
            return Asset::CUSTOMIZER;
        }

        return Asset::FRONTEND;
    }
}
