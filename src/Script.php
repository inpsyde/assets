<?php declare(strict_types=1);

namespace Inpsyde\Assets;

class Script extends BaseAsset implements Asset
{

    public function __construct(string $handle, string $url, array $config = [])
    {
        $config['handle'] = $handle;
        $config['url'] = $url;
        $this->config = array_replace($this->config, $config);
    }

    public function localize(): array
    {
        return (array)($this->config['localize'] ?? []);
    }

    public function inFooter(): bool
    {
        return (bool)($this->config['inFooter'] ?? true);
    }

    public function type(): string
    {
        return self::TYPE_SCRIPT;
    }
}
