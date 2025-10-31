<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptModuleHandler;

class ScriptModule extends BaseAsset implements Asset
{
    use DependencyExtractionTrait;

    /**
     * Data which will be added via ...
     *      - WP_Script::add_data()
     *      - WP_Style::add_data()
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Allows to set additional data via WP_Script::add_data() or WP_Style::add_data().
     *
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
        $this->resolveDependencyExtractionPlugin();

        return parent::version();
    }

    /**
     * {@inheritDoc}
     */
    public function dependencies(): array
    {
        $this->resolveDependencyExtractionPlugin();

        return parent::dependencies();
    }
}
