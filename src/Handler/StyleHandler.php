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

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\OutputFilter\AsyncStyleOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;
use Inpsyde\Assets\Style;

class StyleHandler implements AssetHandler, OutputFilterAwareAssetHandler
{
    use OutputFilterAwareAssetHandlerTrait;

    /**
     * @var \WP_Styles
     */
    protected $wpStyles;

    /**
     * StyleHandler constructor.
     *
     * @param \WP_Styles $wpStyles
     * @param array<string, callable> $outputFilters
     */
    public function __construct(\WP_Styles $wpStyles, array $outputFilters = [])
    {
        $this->withOutputFilter(AsyncStyleOutputFilter::class, new AsyncStyleOutputFilter());
        $this->withOutputFilter(InlineAssetOutputFilter::class, new InlineAssetOutputFilter());
        $this->withOutputFilter(AttributesOutputFilter::class, new AttributesOutputFilter());

        $this->wpStyles = $wpStyles;
        foreach ($outputFilters as $name => $callable) {
            $this->withOutputFilter($name, $callable);
        }
    }

    public function enqueue(Asset $asset): bool
    {
        $this->register($asset);

        if ($asset->enqueue()) {
            wp_enqueue_style($asset->handle());

            return true;
        }

        return false;
    }

    public function register(Asset $asset): bool
    {
        /** @var Style $asset */

        $handle = $asset->handle();
        wp_register_style(
            $handle,
            $asset->url(),
            $asset->dependencies(),
            $asset->version(),
            $asset->media()
        );

        $inlineStyles = $asset->inlineStyles();
        if ($inlineStyles !== null) {
            wp_add_inline_style($handle, implode("\n", $inlineStyles));
        }

        $cssVars = $asset->cssVars();
        if (count($cssVars) > 0) {
            wp_add_inline_style($handle, $asset->cssVarsAsString());
        }

        if (count($asset->data()) > 0) {
            foreach ($asset->data() as $key => $value) {
                $this->wpStyles->add_data($handle, $key, $value);
            }
        }

        return true;
    }

    public function filterHook(): string
    {
        return 'style_loader_tag';
    }
}
