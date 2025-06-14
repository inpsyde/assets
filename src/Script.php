<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptHandler;

class Script extends BaseAsset implements Asset, DataAwareAsset, FilterAwareAsset
{
    use DependencyExtractionTrait;
    use DataAwareTrait;
    use FilterAwareTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $localize = [];

    /**
     * @var array{after:string[], before:string[]}
     */
    protected array $inlineScripts = [
        'after' => [],
        'before' => [],
    ];

    protected bool $inFooter = true;

    /**
     * @var array{domain:string, path:string|null}
     */
    protected array $translation = [
        'domain' => '',
        'path' => null,
    ];

    /**
     * @return array<string, mixed>
     */
    public function localize(): array
    {
        $output = [];
        foreach ($this->localize as $objectName => $data) {
            $output[$objectName] = is_callable($data)
                ? $data()
                : $data;
        }

        return $output;
    }

    /**
     * @param string $objectName
     * @param string|int|array<mixed>|callable $data
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     */
    public function withLocalize(string $objectName, $data): Script
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $this->localize[$objectName] = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function inFooter(): bool
    {
        return $this->inFooter;
    }

    /**
     * @return static
     */
    public function isInFooter(): Script
    {
        $this->inFooter = true;

        return $this;
    }

    /**
     * @return static
     */
    public function isInHeader(): Script
    {
        $this->inFooter = false;

        return $this;
    }

    /**
     * @return array{before:string[], after:string[]}
     */
    public function inlineScripts(): array
    {
        return $this->inlineScripts;
    }

    /**
     * @param string $jsCode
     *
     * @return static
     */
    public function prependInlineScript(string $jsCode): Script
    {
        $this->inlineScripts['before'][] = $jsCode;

        return $this;
    }

    /**
     * @param string $jsCode
     *
     * @return static
     */
    public function appendInlineScript(string $jsCode): Script
    {
        $this->inlineScripts['after'][] = $jsCode;

        return $this;
    }

    /**
     * @return array{domain:string, path:string|null}
     */
    public function translation(): array
    {
        return $this->translation;
    }

    /**
     * @param string $domain
     * @param string|null $path
     *
     * @return static
     */
    public function withTranslation(string $domain = 'default', ?string $path = null): Script
    {
        $this->translation = ['domain' => $domain, 'path' => $path];

        return $this;
    }

    /**
     * Wrapper function to set AsyncScriptOutputFilter as filter.
     *
     * @return static
     * @deprecated use Script::withAttributes(['async' => true]);
     */
    public function useAsyncFilter(): Script
    {
        $this->withAttributes(['async' => true]);

        return $this;
    }

    /**
     * Wrapper function to set DeferScriptOutputFilter as filter.
     *
     * @return static
     * @deprecated use Script::withAttributes(['defer' => true]);
     */
    public function useDeferFilter(): Script
    {
        $this->withAttributes(['defer' => true]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function defaultHandler(): string
    {
        return ScriptHandler::class;
    }

    /**
     * @deprecated when calling Script::version() or Script::dependencies(),
     * we will automatically resolve the dependency extraction plugin files.
     * This method will be removed in future.
     *
     * @see https://github.com/WordPress/gutenberg/tree/master/packages/dependency-extraction-webpack-plugin
     */
    public function useDependencyExtractionPlugin(): Script
    {
        return $this;
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
