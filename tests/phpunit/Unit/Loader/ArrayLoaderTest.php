<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit\Loader;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Loader\ArrayLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;

class ArrayLoaderTest extends AbstractTestCase
{
    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testLoadWithAttributes()
    {
        $expectedAttributes = [
            'data-id' => 'foo',
        ];

        $input = [
            [
                'handle' => 'foo',
                'url' => 'foo.css',
                'location' => Asset::FRONTEND,
                'type' => Style::class,
                'attributes' => $expectedAttributes,
            ],
        ];

        $assets = (new ArrayLoader())
            ->load($input);

        $style = $assets[0];
        static::assertSame($expectedAttributes, $style->attributes());
    }
}
