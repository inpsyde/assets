<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

interface AssetOutputFilter
{
    /**
     * @param string $html
     * @param Asset $asset
     *
     * @return string $html
     */
    public function __invoke(string $html, Asset $asset): string;
}
