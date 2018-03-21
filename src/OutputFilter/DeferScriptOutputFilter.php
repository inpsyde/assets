<?php declare(strict_types=1);

namespace Inpyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

class DeferScriptOutputFilter implements AssetOutputFilter
{

    public function __invoke(string $html, Asset $asset): string
    {
        return str_replace('<script ', '<script defer ', $html);
    }
}
