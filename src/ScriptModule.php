<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptModuleHandler;

class ScriptModule extends BaseAsset implements Asset
{
    use DependencyExtractionTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected bool $dependencyExtractionEnabled = false;

    public function __construct(
        string $handle,
        string $url,
        int $location = Asset::FRONTEND | Asset::ACTIVATE,
        bool $dependencyExtractionEnabled = true
    ) {

        parent::__construct($handle, $url, $location);
        $this->dependencyExtractionEnabled = $dependencyExtractionEnabled;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public function withData(array $data): Asset
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function defaultHandler(): string
    {
        return ScriptModuleHandler::class;
    }

    /**
     * {@inheritDoc}
     */
    public function version(): ?string
    {
        $this->dependencyExtractionEnabled and $this->resolveDependencyExtractionPlugin();

        return parent::version();
    }

    /**
     * {@inheritDoc}
     */
    public function dependencies(): array
    {
        $this->dependencyExtractionEnabled and $this->resolveDependencyExtractionPlugin();

        return parent::dependencies();
    }
}
