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
use Inpsyde\Assets\Loader\EncoreEntrypointsLoader;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class EncoreEntrypointsLoaderTest extends AbstractTestCase
{

    /**
     * @var  vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    public function testLoad()
    {
        $testee = new EncoreEntrypointsLoader();

        $file = $this->mockEntrypointsFile(
            [
                "entrypoints" => [
                    "theme" => [
                        "css" => [
                            "./theme.css",
                        ],
                        "js" => [
                            "./theme.js",
                        ],
                    ],
                ],
            ]
        );

        $assets = $testee->load($file);

        static::assertCount(2, $assets);
        static::assertInstanceOf(Style::class, $assets[0]);
        static::assertInstanceOf(Script::class, $assets[1]);
    }

    public function testLoadWithDependencies()
    {
        $testee = new EncoreEntrypointsLoader();

        $file = $this->mockEntrypointsFile(
            [
                "entrypoints" => [
                    "theme" => [
                        "css" => [
                            "./theme.css",
                            "./theme1.css",
                            "./theme2.css",
                        ],
                    ],
                ],
            ]
        );

        $assets = $testee->load($file);
        static::assertCount(3, $assets);

        /** @var Asset $asset */
        $asset = $assets[1];
        static::assertSame(['theme'], $asset->dependencies());

        $asset = $assets[2];
        static::assertSame(['theme', 'theme-1'], $asset->dependencies());
    }

    private function mockEntrypointsFile(array $json): string
    {
        return vfsStream::newFile('entrypoints.json')
            ->withContent(json_encode($json))
            ->at($this->root)
            ->url();
    }
}
