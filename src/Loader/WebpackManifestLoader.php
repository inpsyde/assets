<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetFactory;
use Inpsyde\Assets\Exception;

/**
 * Implementation of Webpack manifest.json parsing into Assets.
 *
 * @link https://www.npmjs.com/package/webpack-manifest-plugin
 *
 * @package Inpsyde\Assets\Loader
 *
 * @phpstan-import-type AssetConfig from AssetFactory
 * @phpstan-import-type AssetExtensionConfig from AssetFactory
 * @phpstan-type Configuration = AssetConfig&AssetExtensionConfig
 */
class WebpackManifestLoader extends AbstractWebpackLoader
{
    protected function parseData(array $data, string $resource): array
    {
        $directory = trailingslashit(dirname($resource));
        $assets = [];
        foreach ($data as $handle => $fileOrArray) {
            $asset = null;

            if (is_array($fileOrArray)) {
                $asset = $this->handleAsArray($handle, $fileOrArray, $directory);
            }
            if (is_string($fileOrArray)) {
                $asset = $this->handleUsingFileName($handle, $fileOrArray, $directory);
            }

            if ($asset) {
                $assets[] = $asset;
            }
        }

        return $assets;
    }

    /**
     * @param Configuration $configuration
     * @throws Exception\InvalidArgumentException
     * @throws Exception\MissingArgumentException
     */
    protected function handleAsArray(string $handle, array $configuration, string $directory): ?Asset
    {
        $file = $this->extractFilePath($configuration);

        if (!$file) {
            return null;
        }

        $sanitizedFile = $this->sanitizeFileName($file);
        $class = self::resolveClassByExtension($sanitizedFile);

        if (!$class) {
            return null;
        }

        $location = $this->buildLocations($configuration);
        $version = $this->extractVersion($configuration);
        $handle = $this->normalizeHandle($handle);

        $configuration['handle'] = $handle;
        $configuration['url'] = $this->fileUrl($sanitizedFile);
        $configuration['filePath'] = $this->filePath($sanitizedFile, $directory);
        $configuration['type'] = $class;
        $configuration['location'] = $location;
        $configuration['version'] = $version;

        return AssetFactory::create($configuration);
    }

    /**
     * @param Configuration $configuration
     */
    protected function extractFilePath(array $configuration): ?string
    {
        $filePath = $configuration['filePath'] ?? null;
        return is_string($filePath) ? $filePath : null;
    }

    /**
     * @param Configuration $configuration
     */
    protected function extractVersion(array $configuration): ?string
    {
        $version = $configuration['version'] ?? null;

        if (!is_string($version)) {
            $version = '';
        }

        // Autodiscover version is always true by default for the Webpack Manifest Loader
        if ($version) {
            $this->enableAutodiscoverVersion();
        }

        return $version;
    }

    /**
     * @param Configuration $configuration
     */
    protected function buildLocations(array $configuration): int
    {
        $locations = $configuration['location'] ?? null;
        $locations = is_array($locations) ? $locations : [];

        if (count($locations) === 0) {
            return Asset::FRONTEND;
        }

        $locations = array_unique($locations);
        $collector = array_shift($locations);
        $collector = static::resolveLocation("-{$collector}");
        foreach ($locations as $location) {
            $collector |= static::resolveLocation("-{$location}");
        }

        return $collector;
    }

    protected function handleUsingFileName(string $handle, string $file, string $directory): ?Asset
    {
        $handle = $this->normalizeHandle($handle);
        $sanitizedFile = $this->sanitizeFileName($file);
        $fileUrl = $this->fileUrl($sanitizedFile);
        $filePath = $this->filePath($sanitizedFile, $directory);

        return $this->buildAsset($handle, $fileUrl, $filePath);
    }

    protected function fileUrl(string $file): string
    {
        $sanitizedFile = $this->sanitizeFileName($file);
        return (!$this->directoryUrl) ? $file : $this->directoryUrl . $sanitizedFile;
    }

    protected function filePath(string $file, string $directory): string
    {
        $sanitizedFile = $this->sanitizeFileName($file);
        return untrailingslashit($directory) . '/' . ltrim($sanitizedFile, '/');
    }
}
