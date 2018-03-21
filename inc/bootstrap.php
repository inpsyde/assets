<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets;

/**
 * We want to load this file just once. Being loaded by Composer autoload, and being in WordPress context,
 * we have to put special care on this.
 */
if (defined(__NAMESPACE__.'\\BOOTED')) {
    return;
}
const BOOTED = 'inpsyde.assets.booted';
const INITIALIZE = 'inpsyde.assets.initialize';

function assetManager(): AssetManager
{

    static $assetManager;

    if (! $assetManager) {
        // This should run once, but we avoid to break return type, just in case it is called more than once
        $assetManager = apply_filters(
            INITIALIZE,
            (new AssetManager())->useDefaultHandlers()->useDefaultOutputFilters()
        );

        add_action(
            'wp_enqueue_scripts',
            [
                $assetManager,
                'setup',
            ],
            PHP_INT_MAX
        );
    }

    return $assetManager;
}

function assetFactory(): AssetFactory
{

    static $factory;

    if (! $factory) {
        $factory = new AssetFactory();
    }

    return $factory;
}

/**
 * @return string     if SCRIPT_DEBUG=true ".min", otherwise ""
 */
function assetPrefix(): string
{

    return defined('SCRIPT_DEBUG') && \SCRIPT_DEBUG
        ? '.min'
        : '';
}

