<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptHandler;

class Script extends BaseAsset implements Asset
{
    /**
     * @var array
     */
    protected $localize = [];

    /**
     * @var array
     */
    protected $inlinceScripts = [];

    /**
     * @var bool
     */
    protected $inFooter = true;

    /**
     * @var array
     */
    protected $translation = [];

    /**
     * @bool
     */
    protected $useDependencyExtractionPlugin = false;

    /**
     * @var bool
     */
    protected $resolvedDependencyExtractionPlugin = false;

    /**
     * @return array
     */
    public function localize(): array
    {
        $output = [];
        foreach ($this->localize as $objectName => $data) {
            $output[$objectName] = is_callable($data)
                ? $data()
                : $data;
        }

        return (array) $output;
    }

    /**
     * @param string $objectName
     * @param string|int|array|callable $data
     *
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
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
        return (bool) $this->inFooter;
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
     * @return array
     */
    public function inlineScripts(): array
    {
        return (array) $this->inlinceScripts;
    }

    /**
     * @param string $jsCode
     *
     * @return static
     */
    public function prependInlineScript(string $jsCode): Script
    {
        $this->inlinceScripts['before'][] = $jsCode;

        return $this;
    }

    /**
     * @param string $jsCode
     *
     * @return static
     */
    public function appendInlineScript(string $jsCode): Script
    {
        $this->inlinceScripts['after'][] = $jsCode;

        return $this;
    }

    /**
     * @return array
     */
    public function translation(): array
    {
        return (array) $this->translation;
    }

    /**
     * @param string $domain
     * @param string|null $path
     *
     * @return static
     */
    public function withTranslation(string $domain = 'default', string $path = null): Script
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
     * @return string
     */
    protected function defaultHandler(): string
    {
        return ScriptHandler::class;
    }

    /**
     * Automatically resolving dependencies for JS files by searching for a file named
     *
     *  - {fileName}.asset.json
     *  - {fileName}.asset.php
     *
     * which contains an array of dependencies and the version.
     *
     * @see https://github.com/WordPress/gutenberg/tree/master/packages/dependency-extraction-webpack-plugin
     */
    public function useDependencyExtractionPlugin(): Script
    {
        $this->useDependencyExtractionPlugin = true;

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

    /**
     * @return bool
     *
     * @see Script::useDependencyExtractionPlugin()
     */
    protected function resolveDependencyExtractionPlugin(): bool
    {
        if ($this->resolvedDependencyExtractionPlugin) {
            return false;
        }

        $filePath = $this->filePath();
        $depsPhpFile = str_replace(".js", ".asset.php", $filePath);
        $depsJsonFile = str_replace(".js", ".asset.json", $filePath);

        $data = [];
        if (file_exists($depsPhpFile)) {
            $data = @require $depsPhpFile; // phpcs:ignore
        } elseif (file_exists($depsJsonFile)) {
            $data = @json_decode(@file_get_contents($depsJsonFile), true); // phpcs:ignore
        }

        $dependencies = $data['dependencies'] ?? [];
        $version = $data['version'] ?? null;

        $this->withDependencies(...$dependencies);
        if (!$this->version && $version) {
            $this->withVersion($version);
        }

        $this->resolvedDependencyExtractionPlugin = true;

        return true;
    }
}
