<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\ScriptModule;

class ScriptModuleHandler implements AssetHandler
{
    public function enqueue(Asset $asset): bool
    {
        if (!$asset instanceof ScriptModule) {
            return false;
        }
        if (!static::scriptModulesSupported()) {
            return false;
        }

        $this->register($asset);

        if ($asset->enqueue()) {
            wp_enqueue_script_module($asset->handle());

            return true;
        }

        return false;
    }

    public function register(Asset $asset): bool
    {
        if (!$asset instanceof ScriptModule) {
            return false;
        }
        if (!static::scriptModulesSupported()) {
            return false;
        }

        $handle = $asset->handle();

        $this->shareData($asset);

        wp_register_script_module(
            $handle,
            $asset->url(),
            $asset->dependencies(), // @phpstan-ignore-line
            $asset->version(),
        );

        return true;
    }

    protected static function scriptModulesSupported(): bool
    {
        return class_exists('WP_Script_Modules');
    }

    protected function shareData(ScriptModule $asset): void
    {
        $handle = $asset->handle();

        if (!$asset->data()) {
            return;
        }

        add_filter(
            "script_module_data_{$handle}",
            static function (array $data) use ($asset): array {
                return array_merge($data, $asset->data());
            }
        );
    }
}
