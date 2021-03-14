<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\Handler;

use Inpsyde\Assets\Asset;

interface ExternalAssetHandler
{

    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function dequeue(Asset $asset): bool;
}
