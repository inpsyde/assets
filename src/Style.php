<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\StyleHandler;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;

class Style extends BaseAsset implements Asset
{

    /**
     * @return string
     */
    public function media(): string
    {
        return (string) $this->config('media', 'all');
    }

    /**
     * @param string $media
     *
     * @return Style
     */
    public function forMedia(string $media): self
    {
        $this->config['media'] = $media;

        return $this;
    }

    /**
     * @return array
     */
    public function inlineStyles(): ?array
    {
        return $this->config('inline', null);
    }

    /**
     * @link https://codex.wordpress.org/Function_Reference/wp_add_inline_style
     *
     * @param string $inline
     *
     * @return Style
     */
    public function withInlineStyles(string $inline): self
    {
        $this->config['inline'][] = $inline;

        return $this;
    }

    /**
     * Wrapper function to set AsyncStyleOutputFilter as filter.
     *
     * @return Script
     */
    public function useAsyncFilter(): self
    {
        return $this->withFilters(AsyncStyleOutputFilter::class);
    }

    protected function defaultHandler(): string
    {
        return StyleHandler::class;
    }
}
