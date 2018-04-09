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
        $localize = $this->config['localize'] ?? [];
        is_callable($localize) and $localize = $localize();

        return (array) $localize;
    }

    public function inFooter(): bool
    {
        return (bool) ($this->config['inFooter'] ?? true);
    }

    public function type(): string
    {
        return (string) ($this->config['type'] ?? self::TYPE_SCRIPT);
    }
}
