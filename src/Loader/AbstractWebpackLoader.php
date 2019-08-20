<?php declare(strict_types=1); # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Exception\FileNotFoundException;
use Inpsyde\Assets\Exception\InvalidResourceException;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

abstract class AbstractWebpackLoader implements LoaderInterface
{

    /**
     * @var string
     */
    protected $directoryUrl = '';

    /**
     * @param string $directoryUrl optional directory URL which will be used for the Asset.
     *
     * @return AbstractWebpackLoader
     */
    public function withDirectoryUrl(string $directoryUrl): self
    {
        $this->directoryUrl = $directoryUrl;

        return $this;
    }

    /**
     * @param array $data
     * @param string $resource
     *
     * @return array
     */
    abstract protected function parseData(array $data, string $resource): array;

    /**
     * {@inheritDoc}
     *
     * @throws FileNotFoundException
     * @throws InvalidResourceException
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
        $data = @file_get_contents($resource)
            ?: '';
        $data = json_decode($data, true);
        $errorCode = json_last_error();
        if (0 < $errorCode) {
            throw new InvalidResourceException(
                sprintf('Error parsing JSON - %s', $this->getJSONErrorMessage($errorCode))
            );
        }

        return $this->parseData($data, $resource);
    }

    /**
     * Translates JSON_ERROR_* constant into meaningful message.
     *
     * @param int $errorCode
     *
     * @return string Message string
     */
    private function getJSONErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }

    /**
     * @param string $handle
     * @param string $fileUrl
     * @param string $filePath
     *
     * @return Asset|null
     */
    protected function buildAsset(string $handle, string $fileUrl, string $filePath): ?Asset
    {
        [
            'extension' => $extension,
            'filename' => $filename,
        ] = pathinfo($filePath);

        $class = $this->resolveClassByExtension($extension);
        if ($class === '') {
            return null;
        }

        $location = $this->resolveLocation($filename);
        /** @var Asset $asset */
        $asset = new $class($handle, $fileUrl, $location);
        $asset->withFilePath($filePath);
        $asset->canEnqueue(true);

        if ($extension === 'js') {
            $deps = $this->resolveDependencies($filePath);
            /** @var Script $asset */
            $asset->withDependencies(...$deps);
        }

        return $asset;
    }

    /**
     * The "file"-value can contain ...
     *
     *      - URL
     *      - Path to current folder
     *      - Absolute path
     *
     * We try to build a clean path which will be appended to the directoryPath or urlPath.
     *
     * @param string $file
     *
     * @return string
     */
    protected function sanitizeFileName(string $file): string
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
    protected function resolveDependencies(string $filePath): array
    {
        $depsFile = str_replace('.js', '.deps.json', $filePath);
        if (! file_exists($depsFile)) {
            return [];
        }

        $data = @json_decode(@file_get_contents($depsFile));

        return (array) $data;
    }

    /**
     * @param string $extension
     *
     * @return string
     */
    protected function resolveClassByExtension(string $extension): string
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
    protected function resolveLocation(string $fileName): int
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