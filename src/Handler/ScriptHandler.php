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
use Inpsyde\Assets\OutputFilter\AsyncScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\DeferScriptOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;
use Inpsyde\Assets\Script;

class ScriptHandler implements AssetHandler, OutputFilterAwareAssetHandler
{
    use OutputFilterAwareAssetHandlerTrait;

    protected $wpScripts;

    public function __construct(\WP_Scripts $wpScripts, array $outputFilters = [])
    {
        $this->wpScripts = $wpScripts;
        $this->outputFilters = array_merge(
            [
                AsyncScriptOutputFilter::class => new AsyncScriptOutputFilter(),
                DeferScriptOutputFilter::class => new DeferScriptOutputFilter(),
                InlineAssetOutputFilter::class => new InlineAssetOutputFilter(),
            ],
            $outputFilters
        );
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
                wp_localize_script($handle, $name, $args);
            }
        }

        foreach ($asset->inlineScripts() as $location => $data) {
            wp_add_inline_script($handle, implode("\n", $data), $location);
        }

        $translation = $asset->translation();
        if (isset($translation['domain']) && isset($translation['path'])) {
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
