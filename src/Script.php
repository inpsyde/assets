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
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;

class Script extends BaseAsset implements Asset
{

    /**
     * @var bool
     */
    protected $dependenciesResolved = false;

    /**
     * @return array
     */
    public function localize(): array
    {
        $localize = $this->config('localize', []);

        is_callable($localize) and $localize = $localize();

        $output = [];
        foreach ($localize as $objectName => $data) {
            $output[$objectName] = is_callable($data)
                ? $data()
                : $data;
        }

        return (array) $output;
    }

    /**
     * @param string $objectName
     * @param string|int|array|callable $data
     * @return static
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function withLocalize(string $objectName, $data): Script
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $this->config['localize'][$objectName] = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function inFooter(): bool
    {
        return (bool) $this->config('inFooter', true);
    }

    /**
     * @return static
     */
    public function isInFooter(): Script
    {
        $this->config['inFooter'] = true;

        return $this;
    }

    /**
     * @return static
     */
    public function isInHeader(): Script
    {
        $this->config['inFooter'] = false;

        return $this;
    }

    /**
     * @return array
     */
    public function inlineScripts(): array
    {
        return (array) $this->config('inline', []);
    }

    /**
     * @param string $jsCode
     * @return static
     */
    public function prependInlineScript(string $jsCode): Script
    {
        $this->config['inline']['before'][] = $jsCode;

        return $this;
    }

    /**
     * @param string $jsCode
     * @return static
     */
    public function appendInlineScript(string $jsCode): Script
    {
        $this->config['inline']['after'][] = $jsCode;

        return $this;
    }

    /**
     * @return array
     */
    public function translation(): array
    {
        return (array) $this->config('translation', []);
    }

    /**
     * @param string $domain
     * @param string|null $path
     * @return static
     */
    public function withTranslation(string $domain = 'default', string $path = null): Script
    {
        $this->config['translation'] = ['domain' => $domain, 'path' => $path];

        return $this;
    }

    /**
     * Wrapper function to set AsyncScriptOutputFilter as filter.
     *
     * @return static
     */
    public function useAsyncFilter(): Script
    {
        return $this->withFilters(AsyncScriptOutputFilter::class);
    }

    /**
     * Wrapper function to set DeferScriptOutputFilter as filter.
     *
     * @return static
     */
    public function useDeferFilter(): Script
    {
        return $this->withFilters(DeferScriptOutputFilter::class);
    }

    /**
     * @return string
     */
    protected function defaultHandler(): string
    {
        return ScriptHandler::class;
    }

    /**
     * {@inheritDoc}
     */
    public function dependencies(): array
    {
        $filePath = $this->filePath();
        if (!$this->dependenciesResolved) {
            $this->config['dependencies'] = array_merge(
                $this->config['dependencies'],
                $this->resolveDependencies($filePath)
            );
            $this->dependenciesResolved = true;
        }

        return parent::dependencies();
    }

    /**
     * Resolving dependencies for JS files by searching for a {file}.deps.json file which contains
     * an array of dependencies.
     *
     * @param string $filePath
     * @return array
     *
     * @see https://github.com/WordPress/gutenberg/tree/master/packages/dependency-extraction-webpack-plugin
     */
    protected function resolveDependencies(string $filePath): array
    {
        $depsFile = str_replace('.js', '.deps.json', $filePath);
        if (!file_exists($depsFile)) {
            return [];
        }

        $data = @json_decode(@file_get_contents($depsFile)); // phpcs:ignore

        return (array)$data;
    }
}
