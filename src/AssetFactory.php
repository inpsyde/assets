<?php declare(strict_types=1);

namespace Inpsyde\Assets;

final class AssetFactory
{

    const TYPE_TO_CLASS = [
        // Style types
        Asset::TYPE_STYLE => Style::class,
        Asset::TYPE_ADMIN_STYLE => Style::class,
        Asset::TYPE_LOGIN_STYLE => Style::class,
        Asset::TYPE_CUSTOMIZER_STYLE => Style::class,
        // Script types
        Asset::TYPE_SCRIPT => Script::class,
        Asset::TYPE_ADMIN_SCRIPT => Script::class,
        Asset::TYPE_LOGIN_SCRIPT => Script::class,
        Asset::TYPE_CUSTOMIZER_SCRIPT => Script::class,
    ];

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

        $type = $config['type'];
        $handle = $config['handle'];
        $url = $config['url'];

        $class = self::TYPE_TO_CLASS[$type];

        return new $class($handle, $url, $type, $config);
    }

    /**
     * @param array $config
     *
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
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

        if (! isset(self::TYPE_TO_CLASS[$config['type']])) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'The given type "%s" is not allowed.',
                    $config['type']
                )
            );
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
