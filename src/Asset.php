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
    // Types are mapped to hooks.
    const HOOKS_TO_TYPE = [
        'wp_enqueue_scripts' => self::FRONTEND,
        'admin_enqueue_scripts' => self::BACKEND,
        'login_enqueue_scripts' => self::LOGIN,
        'customize_controls_enqueue_scripts' => self::CUSTOMIZER,
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
     * @return array
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
     * @return bool
     */
    public function enqueue(): bool;

    /**
     * Type of asset like "script" or "style".
     *
     * @return int
     */
    public function type(): int;

    /**
     * A list of assigned output filters to change the rendered tag.
     *
     * @return array[]
     */
    public function filters(): array;

    /**
     * Name of the handler to register and enqueue the asset.
     *
     * @return string
     */
    public function handler(): string;
}
