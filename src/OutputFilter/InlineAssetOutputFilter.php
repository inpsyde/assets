<?php

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\FilterAwareAsset;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class InlineAssetOutputFilter implements AssetOutputFilter
{
    /**
     * @param string $html
     * @param FilterAwareAsset $asset
     *
     * @return string
     * @psalm-suppress PossiblyNullArgument
     */
    public function __invoke(string $html, FilterAwareAsset $asset): string
    {
        $filePath = $asset->filePath();

        if ($filePath === '') {
            return $html;
        }

        $content = @file_get_contents($filePath);
        if (!$content) {
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
