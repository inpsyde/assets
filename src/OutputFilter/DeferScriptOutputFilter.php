<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

/**
 * @deprecated use Asset::withAttributes(['defer' => true']);
 */
class DeferScriptOutputFilter implements AssetOutputFilter
{
    public function __invoke(string $html, Asset $asset): string
    {
        return str_replace('<script ', '<script defer ', $html);
    }
}
