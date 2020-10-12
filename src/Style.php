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
     * @return static
     */
    public function forMedia(string $media): Style
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
     * @param string $inline
     * @return static
     *
     * @see https://codex.wordpress.org/Function_Reference/wp_add_inline_style
     */
    public function withInlineStyles(string $inline): Style
    {
        $this->config['inline'][] = $inline;

        return $this;
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
     * @return class-string<\Inpsyde\Assets\Handler\AssetHandler>
     */
    protected function defaultHandler(): string
    {
        return StyleHandler::class;
    }
}
