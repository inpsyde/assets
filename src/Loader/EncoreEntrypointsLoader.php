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

/**
 * Implementation of Symfony's Encore implementation of entrypoints.json which
 * supports splitEntryChunks and hashing.
 *
 * @package Inpsyde\Assets\Loader
 */
class EncoreEntrypointsLoader extends AbstractWebpackLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    protected function parseData(array $data, string $resource): array
    {
        $directory = trailingslashit(dirname($resource));
        /** @var array{entrypoints:array{css?:string[], js?:string[]}} $data */
        $data = $data['entrypoints'] ?? [];

        $assets = [];
        foreach ($data as $handle => $filesByExtension) {
            $files = $filesByExtension['css'] ?? [];
            $assets = array_merge($assets, $this->extractAssets($handle, $files, $directory));

            $files = $filesByExtension['js'] ?? [];
            $assets = array_merge($assets, $this->extractAssets($handle, $files, $directory));
        }

        return $assets;
    }

    /**
     * @param string $handle
     * @param string[] $files
     * @param string $directory
     *
     * @return array
     */
    protected function extractAssets(string $handle, array $files, string $directory): array
    {
        $assets = [];

        foreach ($files as $i => $file) {
            $handle = $i > 0
                ? "{$handle}-{$i}"
                : $handle;

            $sanitizedFile = $this->sanitizeFileName($file);

            $fileUrl = (!$this->directoryUrl)
                ? $file
                : $this->directoryUrl . $sanitizedFile;

            $filePath = $directory . $sanitizedFile;

            $asset = $this->buildAsset($handle, $fileUrl, $filePath);

            if ($asset !== null) {
                $assets[] = $asset;
            }
        }

        foreach ($assets as $i => $asset) {
            $dependencies = array_map(
                static function (Asset $asset): string {
                    return $asset->handle();
                },
                array_slice($assets, 0, $i)
            );
            $asset->withDependencies(...$dependencies);
        }

        return $assets;
    }
}
