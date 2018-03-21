<?php declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

class ScriptHandler implements AssetHandler
{

    protected $wpScripts;

    public function __construct(\WP_Scripts $wpScripts)
    {
        $this->wpScripts = $wpScripts;
    }

    public function enqueue(Asset $asset): bool
    {
        $handle = $asset->handle();

        $this->register($asset);

        if (count($asset->localize()) > 0) {
            foreach ($asset->localize() as $name => $args) {
                wp_localize_script($handle, $name, $args);
            }
        }

        if (count($asset->data()) > 0) {
            foreach ($asset->data() as $key => $value) {
                $this->wpScripts->add_data($handle, $key, $value);
            }
        }

        if ($asset->enqueue()) {
            wp_enqueue_script($handle);
        }

        return true;
    }

    public function register(Asset $asset): bool
    {
        wp_register_script(
            $asset->handle(),
            $asset->url(),
            $asset->dependencies(),
            $asset->version(),
            $asset->inFooter()
        );

        return true;
    }

    public function outputFilterHook(): string
    {
        return 'script_loader_tag';
    }
}
