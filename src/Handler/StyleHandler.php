<?php declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

class StyleHandler implements AssetHandler
{

    protected $wpStyles;

    public function __construct(\WP_Styles $wpStyles)
    {
        $this->wpStyles = $wpStyles;
    }

    public function enqueue(Asset $asset): bool
    {
        $handle = $asset->handle();

        $this->register($asset);

        if (count($asset->data()) > 0) {
            foreach ($asset->data() as $key => $value) {
                $this->wpStyles->add_data($handle, $key, $value);
            }
        }

        if ($asset->enqueue()) {
            wp_enqueue_style($handle);
        }

        return true;
    }

    public function register(Asset $asset): bool
    {
        wp_register_style(
            $asset->handle(),
            $asset->url(),
            $asset->dependencies(),
            $asset->version(),
            $asset->media()
        );

        return true;
    }

    public function outputFilterHook(): string
    {
        return 'style_loader_tag';
    }
}
