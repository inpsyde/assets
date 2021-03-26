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

namespace Inpsyde\Assets;

use Inpsyde\Assets\Loader\PhpFileLoader;
use Inpsyde\Assets\Loader\ArrayLoader;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Script;

/**
 * Class AssetFactory
 *
 * @package Inpsyde\Assets
 */
final class AssetFactory
{
    /**
     * @param array $config
     *
     * @return Asset
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
     *
     * // phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public static function create(array $config): Asset
    {
        self::validateConfig($config);

        $location = $config['location'] ?? Asset::FRONTEND;
        $handle = $config['handle'];
        $url = $config['url'];
        $class = (string) $config['type'];

        $asset = new $class($handle, $url, $location);

        if (!$asset instanceof Asset) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The given class "%s" is not implementing %s',
                    $class,
                    Asset::class
                )
            );
        }

        $propertiesToMethod = [
            'filePath' => 'withFilePath',
            'version' => 'withVersion',
            'location' => 'forLocation',
            'enqueue' => 'canEnqueue',
            'handler' => 'useHandler',
            'condition' => 'withCondition',
            'attributes' => 'withAttributes',
        ];

        if ($class === Script::class) {
            /** @var Script $asset */
            $propertiesToMethod['translation'] = 'withTranslation';

            $inFooter = $config['inFooter'] ?? true;
            $inFooter
                ? $asset->isInFooter()
                : $asset->isInHeader();

            if (!empty($config['inline']['before']) && is_array($config['inline']['before'])) {
                foreach ($config['inline']['before'] as $script) {
                    $asset->prependInlineScript($script);
                }
            }

            if (!empty($config['inline']['after']) && is_array($config['inline']['after'])) {
                foreach ($config['inline']['after'] as $script) {
                    $asset->appendInlineScript($script);
                }
            }
        }

        if ($class === Style::class) {
            $propertiesToMethod['media'] = 'forMedia';
            $propertiesToMethod['inlineStyles'] = 'withInlineStyles';
            $propertiesToMethod['media'] = 'forMedia';
        }

        foreach ($propertiesToMethod as $key => $methodName) {
            if (!isset($config[$key])) {
                continue;
            }
            $asset->{$methodName}($config[$key]);
        }

        $dependencies = $config['dependencies'] ?? null;
        if (is_array($dependencies)) {
            $asset->withDependencies(...$dependencies);
        } elseif (is_scalar($dependencies)) {
            $asset->withDependencies((string) $dependencies);
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
            if (!isset($config[$key])) {
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
     *
     * @throws Exception\FileNotFoundException
     * @deprecated PhpArrayFileLoader::load(string $filePath)
     *
     */
    public static function createFromFile(string $file): array
    {
        $loader = new PhpFileLoader();

        return $loader->load($file);
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws Exception\FileNotFoundException
     * @deprecated ArrayLoader::load(array $data)
     */
    public static function createFromArray(array $data): array
    {
        $loader = new ArrayLoader();

        return $loader->load($data);
    }
}
