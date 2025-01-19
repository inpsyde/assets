<?php

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

/**
 * @deprecated use Asset::withAttributes(['async' => true']);
 */
class AsyncScriptOutputFilter implements AssetOutputFilter
{
    public function __invoke(string $html, Asset $asset): string
    {
        return str_replace('<script ', '<script async ', $html);
    }
}
