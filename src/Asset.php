<?php declare(strict_types=1);

namespace Inpsyde\Assets;

interface Asset
{
    // style types
    const TYPE_STYLE = 'style';
    const TYPE_ADMIN_STYLE = 'admin_style';
    const TYPE_LOGIN_STYLE = "login_style";
    const TYPE_CUSTOMIZER_STYLE = 'customizer_style';
    // script types
    const TYPE_SCRIPT = 'script';
    const TYPE_ADMIN_SCRIPT = 'admin_script';
    const TYPE_LOGIN_SCRIPT = 'login_script';
    const TYPE_CUSTOMIZER_SCRIPT = 'customizer_script';
    /**
     * Types are mapped to hooks.
     *
     * @var array
     */
    const ASSET_HOOKS = [
        self::TYPE_STYLE => 'wp_enqueue_scripts',
        self::TYPE_ADMIN_STYLE => 'admin_enqueue_scripts',
        self::TYPE_LOGIN_STYLE => 'login_enqueue_scripts',
        self::TYPE_CUSTOMIZER_STYLE => 'customize_controls_enqueue_scripts',
        self::TYPE_SCRIPT => 'wp_enqueue_scripts',
        self::TYPE_ADMIN_SCRIPT => 'admin_enqueue_scripts',
        self::TYPE_LOGIN_SCRIPT => 'login_enqueue_scripts',
        self::TYPE_CUSTOMIZER_SCRIPT => 'customize_controls_enqueue_scripts',
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
     * @return string
     */
    public function type(): string;

    /**
     * A list of assigned output filters to change the rendered tag.
     *
     * @return array[]
     */
    public function filters(): array;
}
