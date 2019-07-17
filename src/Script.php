<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\ScriptHandler;
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;

class Script extends BaseAsset implements Asset
{

    /**
     * @return array
     */
    public function localize(): array
    {
        $localize = $this->config('localize', []);

        // @deprecated
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
     * @param string|callable $data
     *
     * @return Script
     * // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function withLocalize(string $objectName, $data): self
    {
        $this->config['localize'][$objectName] = $data;

        return $this;
    }

    public function inFooter(): bool
    {
        return (bool) $this->config('inFooter', true);
    }

    public function isInFooter(): self
    {
        $this->config['inFooter'] = true;

        return $this;
    }

    public function isInHeader(): self
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
     *
     * @return Script
     */
    public function prependInlineScript(string $jsCode): self
    {
        $this->config['inline']['before'][] = $jsCode;

        return $this;
    }

    /**
     * @param string $jsCode
     *
     * @return Script
     */
    public function appendInlineScript(string $jsCode): self
    {
        $this->config['inline']['after'][] = $jsCode;

        return $this;
    }

    public function translation(): array
    {
        return (array) $this->config('translation', []);
    }

    public function withTranslation(string $domain = 'default', string $path = null): self
    {
        $this->config['translation'] = ['domain' => $domain, 'path' => $path];

        return $this;
    }

    /**
     * Wrapper function to set AsyncScriptOutputFilter as filter.
     *
     * @return Script
     */
    public function useAsyncFilter(): self
    {
        return $this->withFilters(AsyncScriptOutputFilter::class);
    }

    /**
     * Wrapper function to set DeferScriptOutputFilter as filter.
     *
     * @return Script
     */
    public function useDeferFilter(): self
    {
        return $this->withFilters(DeferScriptOutputFilter::class);
    }

    protected function defaultHandler(): string
    {
        return ScriptHandler::class;
    }
}
