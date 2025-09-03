<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

interface DataAwareAsset extends Asset
{
    /**
     * @return array<mixed>
     */
    public function data(): array;

    /**
     * Add a conditional tag for your Asset.
     *
     * @param string $condition
     *
     * @return Asset
     *
     * @see https://developer.wordpress.org/reference/functions/wp_script_add_data/#comment-1007
     */
    public function withCondition(string $condition): Asset;
}
