<?php declare(strict_types=1);

namespace Inpsyde\Assets;

final class AssetFactory
{

    protected $types = [
        Asset::TYPE_STYLE => Style::class,
        Asset::TYPE_SCRIPT => Script::class,
    ];

    /**
     * @param array $config
     *
     * @return Asset
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
     */
    public function create(array $config): Asset
    {
        $this->validateConfig($config);

        $type = $config['type'];
        $handle = $config['handle'];
        $url = $config['url'];

        $class = $this->types[$type];

        return new $class($handle, $url, $config);
    }

    /**
     * @param array $config
     *
     * @throws Exception\MissingArgumentException
     * @throws Exception\InvalidArgumentException
     */
    private function validateConfig(array $config)
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

        if (! isset($this->types[$config['type']])) {
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
    public function createFromFile(string $file): array
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

        return $this->createFromArray($data);
    }

    public function createFromArray(array $data): array
    {
        return array_map(
            [
                $this,
                'create',
            ],
            $data
        );
    }
}
