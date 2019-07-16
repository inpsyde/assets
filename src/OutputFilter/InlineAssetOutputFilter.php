<?php declare(strict_types=1); # -*- coding: utf-8 -*-
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
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class InlineAssetOutputFilter implements AssetOutputFilter
{

    public function __invoke(string $html, Asset $asset): string
    {
        $filePath = $asset->filePath();

        if ($filePath === '') {
            return $html;
        }

        $content = @file_get_contents($filePath);
        if (! $content) {
            return $html;
        }

        if ($asset instanceof Script) {
            return sprintf(
                '<script data-version="%1$s" data-id="%2$s">%3$s</script>',
                $asset->version(),
                $asset->handle(),
                $content
            );
        }

        if ($asset instanceof Style) {
            return sprintf(
                '<style data-version="%1$s" data-id="%2$s">%3$s</style>',
                $asset->version(),
                $asset->handle(),
                $content
            );
        }

        return $html;
    }
}
