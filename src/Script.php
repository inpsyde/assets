<?php declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptHandler;

class Script extends BaseAsset implements Asset
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
        return (string) ($this->config['type'] ?? self::FRONTEND);
    }

    public function handler(): string
    {
        return (string) ($this->config['handler'] ?? ScriptHandler::class);
    }
}
