<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;

interface Asset
{
    // Location types
    public const FRONTEND = 2;
    public const BACKEND = 4;
    public const CUSTOMIZER = 8;
    public const LOGIN = 16;
    public const BLOCK_EDITOR_ASSETS = 32;
    public const BLOCK_ASSETS = 64;
    public const CUSTOMIZER_PREVIEW = 128;
    public const ACTIVATE = 256;
    // Hooks
    public const HOOK_FRONTEND = 'wp_enqueue_scripts';
    public const HOOK_BACKEND = 'admin_enqueue_scripts';
    public const HOOK_LOGIN = 'login_enqueue_scripts';
    public const HOOK_CUSTOMIZER = 'customize_controls_enqueue_scripts';
    public const HOOK_CUSTOMIZER_PREVIEW = 'customize_preview_init';
    public const HOOK_BLOCK_ASSETS = 'enqueue_block_assets';
    public const HOOK_BLOCK_EDITOR_ASSETS = 'enqueue_block_editor_assets';
    public const HOOK_ACTIVATE = 'activate_wp_head';
    /**
     * Hooks to Locations map
     * @var array<string,int>
     */
    public const HOOK_TO_LOCATION = [
        Asset::HOOK_FRONTEND => Asset::FRONTEND,
        Asset::HOOK_BACKEND => Asset::BACKEND,
        Asset::HOOK_LOGIN => Asset::LOGIN,
        Asset::HOOK_CUSTOMIZER => Asset::CUSTOMIZER,
        Asset::HOOK_CUSTOMIZER_PREVIEW => Asset::CUSTOMIZER_PREVIEW,
        Asset::HOOK_BLOCK_ASSETS => Asset::BLOCK_ASSETS,
        Asset::HOOK_BLOCK_EDITOR_ASSETS => Asset::BLOCK_EDITOR_ASSETS,
        Asset::HOOK_ACTIVATE => Asset::ACTIVATE,
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
     * @return static
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
     * @return static
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
     * @return static
     */
    public function withVersion(string $version): Asset;

    /**
     * @return bool
     */
    public function enqueue(): bool;

    /**
     * @param bool|callable $enqueue
     *
     * @return static
     *
     *  phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     */
    public function canEnqueue($enqueue): Asset;

    /**
     * Location where the asset is enqueued.
     *
     * @return int
     *
     * @example Asset::FRONTEND | Asset::BACKEND
     * @example Asset::FRONTEND
     */
    public function location(): int;

    /**
     * Define a location based on Asset location types.
     *
     * @param int $location
     *
     * @return static
     */
    public function forLocation(int $location): Asset;

    /**
     * Name of the handler class to register and enqueue the asset.
     *
     * @return class-string<AssetHandler>
     */
    public function handler(): string;

    /**
     * @param class-string<AssetHandler> $handler
     *
     * @return static
     */
    public function useHandler(string $handler): Asset;
}
