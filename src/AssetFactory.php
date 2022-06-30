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

use Inpsyde\Assets\Exception\InvalidArgumentException;
use Inpsyde\Assets\Loader\PhpFileLoader;
use Inpsyde\Assets\Loader\ArrayLoader;

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
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     * @psalm-suppress MixedArgument, MixedMethodCall
     */
    public static function create(array $config): Asset
    {
        $config = self::validateConfig($config);

        $location = $config['location'] ?? Asset::FRONTEND;
        $handle = $config['handle'];
        $url = $config['url'];

        $class = (string) $config['type'];
        if (!class_exists($class)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The given class "%s" does not exists.',
                    $class
                )
            );
        }

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

        if ($asset instanceof Script) {
            foreach ($config['localize'] as $objectName => $data) {
                $asset->withLocalize((string) $objectName, $data);
            }

            if (isset($config['translation'])) {
                /** @var array{domain:string, path:?string} $translations */
                $translations = $config['translation'];
                $asset->withTranslation(
                    (string) $translations['domain'],
                    $translations['path'] ?? null
                );
            }

            $inFooter = $config['inFooter'] ?? true;
            $inFooter
                ? $asset->isInFooter()
                : $asset->isInHeader();

            if (!empty($config['inline']['before']) && is_array($config['inline']['before'])) {
                foreach ($config['inline']['before'] as $script) {
                    $asset->prependInlineScript((string) $script);
                }
            }

            if (!empty($config['inline']['after']) && is_array($config['inline']['after'])) {
                foreach ($config['inline']['after'] as $script) {
                    $asset->appendInlineScript((string) $script);
                }
            }
        }

        if ($asset instanceof Style) {
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
     * @return array
     *
     * @throws Exception\MissingArgumentException
     */
    private static function validateConfig(array $config): array
    {
        self::ensureRequiredConfigFields($config);
        $config = self::normalizeVersionConfig($config);
        $config = self::normalizeTranslationConfig($config);
        $config = self::normalizeLocalizeConfig($config);

        return $config;
    }

    private static function ensureRequiredConfigFields(array $config): void
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

    private static function normalizeVersionConfig(array $config): array
    {
        // some existing configurations uses time() as version parameter which leads to
        // fatal errors since 2.5
        if (isset($config['version'])) {
            $config['version'] = (string) $config['version'];
        }

        return $config;
    }

    private static function normalizeTranslationConfig(array $config): array
    {
        if (!isset($config['translation'])) {
            return $config;
        }

        if (is_string($config['translation'])) {
            // backward compatibility
            $config['translation'] = [
                'domain' => $config['translation'],
                'path' => null,
            ];

            return $config;
        }

        if (!is_array($config['translation'])) {
            throw new InvalidArgumentException(
                "Config key <code>translation</code> must be of type string or array"
            );
        }

        if (!isset($config['translation']['domain'])) {
            throw new Exception\MissingArgumentException(
                'Config key <code>translation[domain]</code> is missing.'
            );
        }

        if (!isset($config['translation']['path'])) {
            $config['translation']['path'] = null;
        }

        return $config;
    }

    private static function normalizeLocalizeConfig(array $config): array
    {
        if (!isset($config['localize'])) {
            $config['localize'] = [];

            return $config;
        }
        if (is_callable($config['localize'])) {
            $config['localize'] = $config['localize']();
        }
        if (!is_array($config['localize'])) {
            throw new InvalidArgumentException(
                'Config key <code>localize</code> must evaluate as an array'
            );
        }

        return $config;
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
