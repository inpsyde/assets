<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

class ScriptModuleHandler implements AssetHandler
{
    public function enqueue(Asset $asset): bool
    {
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
        if (!static::scriptModulesSupported()) {
            return false;
        }

        $handle = $asset->handle();

        wp_register_script_module(
            $handle,
            $asset->url(),
            $asset->dependencies(), // @phpstan-ignore-line
            $asset->version()
        );

        return true;
    }

    public static function scriptModulesSupported(): bool
    {
        return function_exists('wp_register_script_module')
            && function_exists('wp_enqueue_script_module')
            && function_exists('wp_deregister_script_module')
            && function_exists('wp_dequeue_script_module');
    }
}
