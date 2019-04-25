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
    // Hooks are mapped to types.
    const HOOK_TO_LOCATION = [
        'wp_enqueue_scripts' => self::FRONTEND,
        'admin_enqueue_scripts' => self::BACKEND,
        'login_enqueue_scripts' => self::LOGIN,
        'customize_controls_enqueue_scripts' => self::CUSTOMIZER,
        'enqueue_block_editor_assets' => self::BLOCK_EDITOR_ASSETS,
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
     * @return array
     * @example [ 'conditional' => 'IE 8' ]
     *
     */
    public function data(): array;

    /**
     *
     * @return bool|callable
     * @example     function() { return is_single(); }
     *
     * @example     'is_single'
     */
    public function enqueue(): bool;

    /**
     * Location where the asset is enqueued.
     *
     * @return int
     * @example     Asset::FRONTEND | Asset::BACKEND
     *
     * @example     Asset::FRONTEND
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
