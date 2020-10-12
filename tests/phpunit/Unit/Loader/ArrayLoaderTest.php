<?php

declare(strict_types=1);

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\ArrayLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ArrayLoaderTest extends AbstractTestCase
{

    public function testLoad()
    {
        $input = [
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => Asset::FRONTEND,
                'type' => Style::class,
            ],
            [
                'handle' => 'bar',
                'url' => 'bar.js',
                'location' => Asset::FRONTEND,
                'type' => Script::class,
            ],
        ];

        $assets = (new ArrayLoader())->load($input);
        static::assertCount(2, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
    }

    public function testLoadDisabledAutodiscoverVersion()
    {
        $input = [
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => Asset::FRONTEND,
                'type' => Style::class,
            ],
        ];

        $assets = (new ArrayLoader())
            ->disableAutodiscoverVersion()
            ->load($input);

        static::assertCount(1, $assets);

        /** @var Asset $asset */
        $asset = $assets[0];
        static::assertNull($asset->version());
    }
}
