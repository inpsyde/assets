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
use Inpsyde\Assets\BaseAsset;
use Inpsyde\Assets\ConfigureAutodiscoverVersionTrait;
use Inpsyde\Assets\Exception\FileNotFoundException;
use Inpsyde\Assets\Exception\InvalidResourceException;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

abstract class AbstractWebpackLoader implements LoaderInterface
{
    use ConfigureAutodiscoverVersionTrait;

    /**
     * @var string
     */
    protected $directoryUrl = '';

    /**
     * @param string $directoryUrl optional directory URL which will be used for the Asset
     *
     * @return static
     */
    public function withDirectoryUrl(string $directoryUrl): AbstractWebpackLoader
    {
        $this->directoryUrl = $directoryUrl;

        return $this;
    }

    /**
     * @param array<string, string> $data
     * @param string $resource
     *
     * @return array
     */
    abstract protected function parseData(array $data, string $resource): array;

    /**
     * @param mixed $resource
     *
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * @psalm-suppress MixedArgument
     */
    public function load($resource): array
    {
        if (!is_string($resource) || !is_readable($resource)) {
            throw new FileNotFoundException(
                sprintf(
                    'The given file "%s" does not exists or is not readable.',
                    (string) $resource
                )
            );
        }

        $data = @file_get_contents($resource)
            ?: ''; // phpcs:ignore
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
        $extensionsToClass = [
            'css' => Style::class,
            'js' => Script::class,
        ];

        /** @var array{filename?:string, extension?:string} $pathInfo */
        $pathInfo = pathinfo($filePath);
        $filename = $pathInfo['filename'] ?? '';
        $extension = $pathInfo['extension'] ?? '';

        if (!in_array($extension, array_keys($extensionsToClass), true)) {
            return null;
        }

        $class = $extensionsToClass[$extension];

        /** @var Asset|BaseAsset $asset */
        $asset = new $class($handle, $fileUrl, $this->resolveLocation($filename));
        $asset->withFilePath($filePath);
        $asset->canEnqueue(true);

        if ($asset instanceof BaseAsset) {
            $this->autodiscoverVersion
                ? $asset->enableAutodiscoverVersion()
                : $asset->disableAutodiscoverVersion();
        }

        return $asset;
    }

    /**
     * The "file"-value can contain:
     *  - URL
     *  - Path to current folder
     *  - Absolute path
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

        // the "file"-value can contain "./file.css" or "/file.css".

        return ltrim($parsedUrl['path'] ?? $file, './');
    }

    /**
     * Internal function to resolve a location for a given file name.
     *
     * @param string $fileName
     *
     * @return int
     *
     * @example foo-customizer.css  -> Asset::CUSTOMIZER
     * @example foo-block.css       -> Asset::BLOCK_EDITOR_ASSETS
     * @example foo-login.css       -> Asset::LOGIN
     * @example foo.css             -> Asset::FRONTEND
     * @example foo-backend.css     -> Asset::BACKEND
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

        if (stristr($fileName, '-customizer-preview')) {
            return Asset::CUSTOMIZER_PREVIEW;
        }

        if (stristr($fileName, '-customizer')) {
            return Asset::CUSTOMIZER;
        }

        return Asset::FRONTEND;
    }
}
