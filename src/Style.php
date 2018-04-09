<?php declare(strict_types=1);

namespace Inpsyde\Assets;

class Style extends BaseAsset implements Asset
{

    public function __construct(string $handle, string $url, string $type = Asset::TYPE_STYLE, array $config = [])
    {
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
        return (string) ($this->config['type'] ?? self::TYPE_STYLE);
    }
}
