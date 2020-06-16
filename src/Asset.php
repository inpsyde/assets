<?php

declare(strict_types=1);

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

    // location types
    const FRONTEND = 2;
    const BACKEND = 4;
    const CUSTOMIZER = 8;
    const LOGIN = 16;
    const BLOCK_EDITOR_ASSETS = 32;
    // hooks
    const HOOK_FRONTEND = 'wp_enqueue_scripts';
    const HOOK_BACKEND = 'admin_enqueue_scripts';
    const HOOK_LOGIN = 'login_enqueue_scripts';
    const HOOK_CUSTOMIZER = 'customize_controls_enqueue_scripts';
    const HOOK_BLOCK_EDITOR_ASSETS = 'enqueue_block_editor_assets';
    // Hooks are mapped to location types
    const HOOK_TO_LOCATION = [
        Asset::HOOK_FRONTEND => Asset::FRONTEND,
        Asset::HOOK_BACKEND => Asset::BACKEND,
        Asset::HOOK_LOGIN => Asset::LOGIN,
        Asset::HOOK_CUSTOMIZER => Asset::CUSTOMIZER,
        Asset::HOOK_BLOCK_EDITOR_ASSETS => Asset::BLOCK_EDITOR_ASSETS,
    ];

    /**
     * Contains the full url to file.
     *
     * @return string
     */
    public function url(): string;

    /**
     * Returns the full file path to the asset.
     *
     * @return string
     */
    public function filePath(): string;

    /**
     * Define the full filePath to the Asset.
     *
     * @param string $filePath
     *
     * @return Asset
     */
    public function withFilePath(string $filePath): Asset;

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
     * @param string ...$dependencies
     *
     * @return Script|Style
     */
    public function withDependencies(string ...$dependencies): Asset;

    /**
     * The current version of the asset.
     *
     * @return string|null
     */
    public function version(): ?string;

    /**
     * @param string $version
     *
     * @return Asset|Script|Style
     */
    public function withVersion(string $version): Asset;

    /**
     *
     * @return bool|callable
     * @example     function() { return is_single(); }
     *
     * @example     'is_single'
     */
    public function enqueue(): bool;

    /**
     * @param bool|callable $enqueue
     *
     * @return Asset|Script|Style
     *
     * // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function canEnqueue($enqueue): Asset;

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
     * Define a location based on Asset location types.
     *
     * @param int $location
     *
     * @return Asset|Script|Style
     */
    public function forLocation(int $location): Asset;

    /**
     * A list of assigned output filters to change the rendered tag.
     *
     * @return callable|OutputFilter\AssetOutputFilter[]
     */
    public function filters(): array;

    /**
     * @param callable|OutputFilter\AssetOutputFilter ...$filters
     *
     * @return Asset|Script|Style
     */
    public function withFilters(...$filters): Asset;

    /**
     * Name of the handler to register and enqueue the asset.
     *
     * @return string
     */
    public function handler(): string;

    /**
     * @param string $handler
     *
     * @return Asset|Script|Style
     */
    public function useHandler(string $handler): Asset;

    /**
     * @return array
     */
    public function data(): array;

    /**
     * Add a condtional tag for your Asset.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_script_add_data/#comment-1007
     *
     * @param string $condition
     *
     * @return Asset|Script|Style
     */
    public function withCondition(string $condition): Asset;
}
