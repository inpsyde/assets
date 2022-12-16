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

use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use WP_Scripts;

class ScriptHandler implements AssetHandler, OutputFilterAwareAssetHandler
{
    use OutputFilterAwareAssetHandlerTrait;

    /**
     * @var \WP_Scripts
     */
    protected $wpScripts;

    /**
     * ScriptHandler constructor.
     *
     * @param \WP_Scripts $wpScripts
     * @param array<string, callable> $outputFilters
     */
    public function __construct(WP_Scripts $wpScripts, array $outputFilters = [])
    {
        $this->withOutputFilter(AsyncScriptOutputFilter::class, new AsyncScriptOutputFilter());
        $this->withOutputFilter(DeferScriptOutputFilter::class, new DeferScriptOutputFilter());
        $this->withOutputFilter(InlineAssetOutputFilter::class, new InlineAssetOutputFilter());
        $this->withOutputFilter(AttributesOutputFilter::class, new AttributesOutputFilter());

        $this->wpScripts = $wpScripts;
        foreach ($outputFilters as $name => $callable) {
            $this->withOutputFilter($name, $callable);
        }
    }

    public function enqueue(Asset $asset): bool
    {
        $this->register($asset);

        if ($asset->enqueue()) {
            wp_enqueue_script($asset->handle());

            return true;
        }

        return false;
    }

    public function register(Asset $asset): bool
    {
        /** @var Script $asset */

        $handle = $asset->handle();

        wp_register_script(
            $handle,
            $asset->url(),
            $asset->dependencies(),
            $asset->version(),
            $asset->inFooter()
        );

        if (count($asset->localize()) > 0) {
            foreach ($asset->localize() as $name => $args) {
                /**
                 * Actually it is possible to use $args as scalar value for
                 * \WP_Scripts::localize() - but it will produce a _doing_it_wrong().
                 *
                 * @psalm-suppress MixedArgument
                 */
                wp_localize_script($handle, $name, $args);
            }
        }

        foreach ($asset->inlineScripts() as $location => $data) {
            if (count($data) > 0) {
                wp_add_inline_script($handle, implode("\n", $data), $location);
            }
        }

        $translation = $asset->translation();
        if ($translation['domain'] !== '') {
            /**
             * The $path is allowed to be "null"- or a "string"-value.
             * @psalm-suppress PossiblyNullArgument
             */
            wp_set_script_translations($handle, $translation['domain'], $translation['path']);
        }

        if (count($asset->data()) > 0) {
            foreach ($asset->data() as $key => $value) {
                $this->wpScripts->add_data($handle, $key, $value);
            }
        }

        return true;
    }

    public function filterHook(): string
    {
        return 'script_loader_tag';
    }
}
