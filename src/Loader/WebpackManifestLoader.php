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
    protected function parseData(array $data, string $resource): array
    {
        $directory = trailingslashit(dirname($resource));
        $assets = [];
        foreach ($data as $handle => $file) {
            $handle = $this->sanitizeHandle($handle);
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

        return $assets;
    }
}
