<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Loader;

/**
 * Implementation of Webpack manifest.json parsing into Assets.
 *
 * @link https://www.npmjs.com/package/webpack-manifest-plugin
 *
 * @package Inpsyde\Assets\Loader
 */
class WebpackManifestLoader extends AbstractWebpackLoader
{
    /**
     * {@inheritDoc}
     */
    protected function parseData(array $data, string $resource): array
    {
        $directory = trailingslashit(dirname($resource));
        $assets = [];
        foreach ($data as $handle => $file) {
            // It can be possible, that the "handle"-key is a filepath.
            $handle = pathinfo($handle, PATHINFO_FILENAME);

            $sanitizedFile = $this->sanitizeFileName($file);

            $fileUrl = (! $this->directoryUrl)
                ? $file
                : $this->directoryUrl . $sanitizedFile;

            $filePath = $directory . $sanitizedFile;

            $asset = $this->buildAsset($handle, $fileUrl, $filePath);
            if ($asset !== null) {
                $assets[] = $asset;
            }
        }

        return $assets;
    }
}
