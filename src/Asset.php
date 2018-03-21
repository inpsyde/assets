<?php declare(strict_types=1);

namespace Inpsyde\Assets;

interface Asset
{

    const TYPE_SCRIPT = 'script';
    const TYPE_STYLE = 'style';

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
     * A list of assigned output filters to change the rendered tag.
     *
     * @return array[]
     */
    public function filters(): array;

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
}
