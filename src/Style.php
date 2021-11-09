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

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;

class Style extends BaseAsset implements Asset
{
    /**
     * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/link#attr-media
     *
     * @var string
     */
    protected $media = 'all';

    /**
     * @var string[]|null
     */
    protected $inlineStyles = null;

    /**
     * @var array<string, array<string, string>>
     */
    protected $cssVars = [];

    /**
     * @return string
     */
    public function media(): string
    {
        return $this->media;
    }

    /**
     * @param string $media
     *
     * @return static
     */
    public function forMedia(string $media): Style
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function inlineStyles(): ?array
    {
        return $this->inlineStyles;
    }

    /**
     * @param string $inline
     *
     * @return static
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_add_inline_style
     */
    public function withInlineStyles(string $inline): Style
    {
        if (!$this->inlineStyles) {
            $this->inlineStyles = [];
        }

        $this->inlineStyles[] = $inline;

        return $this;
    }

    /**
     * Add custom CSS properties (CSS vars) to an element.
     * Those custom CSS vars will be enqueued with inline style
     * to your handle. Variables will be automatically prefixed
     * with '--'.
     *
     * @param string $element
     * @param array<string, string> $vars
     *
     * @return $this
     *
     * @example Style::withCssVars('.some-element', ['--white' => '#fff']);
     * @example Style::withCssVars('.some-element', ['white' => '#fff']);
     */
    public function withCssVars(string $element, array $vars): Style
    {
        if (!isset($this->cssVars[$element])) {
            $this->cssVars[$element] = [];
        }

        foreach ($vars as $key => $value) {
            $key = substr($key, 0, 2) === '--'
                ? $key
                : '--' . $key;

            $this->cssVars[$element][$key] = $value;
        }

        return $this;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function cssVars(): array
    {
        return $this->cssVars;
    }

    /**
     * @return string
     */
    public function cssVarsAsString(): string
    {
        $return = '';
        foreach ($this->cssVars() as $element => $vars) {
            $values = '';
            foreach ($vars as $key => $value) {
                $values .= sprintf('%1$s:%2$s;', $key, $value);
            }
            $return .= sprintf('%1$s{%2$s}', $element, $values);
        }

        return $return;
    }

    /**
     * Wrapper function to set AsyncStyleOutputFilter as filter.
     *
     * @return static
     */
    public function useAsyncFilter(): Style
    {
        return $this->withFilters(AsyncStyleOutputFilter::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function defaultHandler(): string
    {
        return StyleHandler::class;
    }
}
