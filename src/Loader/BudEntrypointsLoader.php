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

namespace Inpsyde\Assets\Loader;

class BudEntrypointsLoader extends EncoreEntrypointsLoader
{
    protected function parseData(array $data, string $resource, array $entrypoints = []): array
    {
        $directory = trailingslashit(dirname($resource));
        /** @var array{css:string[], js:string[]} $data */
        $data = is_array($data) ? $data : [];
        if (!empty($entrypoints)) {
            $data = array_filter($data, static function (string $handle) use ($entrypoints) {
                return in_array($handle, $entrypoints, true);
            }, ARRAY_FILTER_USE_KEY);
        }

        $assets = [];
        foreach ($data as $handle => $filesByExtension) {
            $files = $filesByExtension['css'] ?? [];
            $assets = array_merge($assets, $this->extractAssets($handle, $files, $directory));

            $files = $filesByExtension['js'] ?? [];
            $assets = array_merge($assets, $this->extractAssets($handle, $files, $directory));
        }

        return $assets;
    }
}
