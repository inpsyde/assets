<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

interface Asset
{

    const FRONTEND = 1;
    const BACKEND = 2;
    const CUSTOMIZER = 3;
    const LOGIN = 4;
    // triggered when Gutenberg Editor is loading.
    const BLOCK_EDITOR_ASSETS = 5;
    // triggered when Gutenberg Editor is loading *and* on frontend.
    const BLOCK_ASSETS = 6;
    // Hooks are mapped to types.
    const HOOK_TO_LOCATION = [
        'wp_enqueue_scripts' => self::FRONTEND,
        'admin_enqueue_scripts' => self::BACKEND,
        'login_enqueue_scripts' => self::LOGIN,
        'customize_controls_enqueue_scripts' => self::CUSTOMIZER,
        'enqueue_block_editor_assets' => self::BLOCK_EDITOR_ASSETS,
        'enqueue_block_assets' => self::BLOCK_ASSETS,
    ];

    /**
     * Contains the full url to file.
     *
     * @return string
     */
    public function url(): string;

    /**
     * Name of the given asset.
     *
     * @return string
     */
    public function handle(): string;

    /**
     * A list of handle-dependencies.
     *
     * @return string[]
     */
    public function dependencies(): array;

    /**
     * The current version of the asset.
     *
     * @return string
     */
    public function version(): string;

    /**
     * Assigned additional data.
     *
     * @example [ 'conditional' => 'IE 8' ]
     *
     * @return array
     */
    public function data(): array;

    /**
     *
     * @example     'is_single'
     * @example     function() { return is_single(); }
     *
     * @return bool|callable
     */
    public function enqueue(): bool;

    /**
     * Location where the asset is enqueued.
     *
     * @example     Asset::FRONTEND
     * @example     Asset::FRONTEND | Asset::BACKEND
     *
     * @return int
     */
    public function location(): int;

    /**
     * A list of assigned output filters to change the rendered tag.
     *
     * @return callable|OutputFilter\AssetOutputFilter[]
     */
    public function filters(): array;

    /**
     * Name of the handler to register and enqueue the asset.
     *
     * @return string
     */
    public function handler(): string;
}
