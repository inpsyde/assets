<?php declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\StyleHandler;

class Style extends BaseAsset implements Asset
{

    public function __construct(
        string $handle,
        string $url,
        string $type = Asset::FRONTEND,
        array $config = []
    ) {

        $config['handle'] = $handle;
        $config['url'] = $url;
        $config['type'] = $type;

        $this->config = array_replace($this->config, $config);
    }

    public function media(): string
    {
        return (string) ($this->config['media'] ?? 'all');
    }

    public function type(): string
    {
        return (string) ($this->config['type'] ?? self::FRONTEND);
    }

    public function handler(): string
    {
        return (string) ($this->config['handler'] ?? StyleHandler::class);
    }
}
