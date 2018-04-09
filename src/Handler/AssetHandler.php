<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

interface AssetHandler
{

    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function register(Asset $asset): bool;

    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function enqueue(Asset $asset): bool;
}
