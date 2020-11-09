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

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey\Functions;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\BaseAsset;
use org\bovigo\vfs\vfsStream;

class BaseAssetTest extends AbstractTestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('tmp');
        parent::setUp();
    }

    /**
     * @test
     */
    public function testBasic(): void
    {
        $expectedHandle = bin2hex(random_bytes(4));
        $expectedUrl = "{$expectedHandle}.js";

        $asset = new class ($expectedHandle, $expectedUrl) extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertSame($expectedUrl, $asset->url());
        static::assertSame($expectedHandle, $asset->handle());
        static::assertTrue($asset->enqueue());
        static::assertEmpty($asset->filters());
        static::assertEmpty($asset->data());
        static::assertSame(Asset::FRONTEND, $asset->location());
    }

    /**
     * @test
     */
    public function testVersion(): void
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        $fileStub = vfsStream::newFile('style.css')
            ->withContent('body { background: white; }')
            ->at($this->root);

        $asset->withFilePath($fileStub->url());

        // If automatic discovering of version is disabled and no version is set --> ''
        $asset->disableAutodiscoverVersion();
        static::assertSame(null, $asset->version());

        $asset->enableAutodiscoverVersion();
        $version = $asset->version();

        static::assertTrue($version && is_numeric($version));

        // if we set a version, the version should be returned.
        $asset->withVersion('foo');
        static::assertEquals('foo', $asset->version());
    }

    /**
     * @test
     */
    public function testNoVersion(): void
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        $asset->withVersion('');

        static::assertSame('', $asset->version());
    }

    /**
     * @test
     */
    public function testFilePath(): void
    {
        Functions\expect('set_url_scheme')->once()->andReturnFirstArg();
        Functions\expect('get_stylesheet_directory_uri')->once()->andReturn('https://example.com');
        Functions\expect('get_template_directory_uri')->once()->andReturn('https://example.com');
        Functions\expect('get_stylesheet_directory')->once()->andReturn($this->root->url());

        $asset = new class ('foo', 'https://example.com/style.css') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        vfsStream::newFile('style.css')
            ->withContent('body { background: white; }')
            ->at($this->root);

        $expectedFilePath = $this->root->url() . '/style.css';

        static::assertSame($expectedFilePath, $asset->filePath());
        static::assertSame($expectedFilePath, $asset->filePath());
    }

    /**
     * @test
     */
    public function testFilePathFails(): void
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        Functions\expect('set_url_scheme')->once()->andThrow(new \Exception());

        static::assertSame('', $asset->filePath());
    }

    /**
     * @test
     */
    public function testDependencies(): void
    {
        /** @var BaseAsset $testee */
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertEmpty($asset->dependencies());

        $asset->withDependencies('foo');
        static::assertEquals(['foo'], $asset->dependencies());

        $asset->withDependencies('bar', 'baz');
        static::assertEquals(['foo', 'bar', 'baz'], $asset->dependencies());

        // Adding "foo" again shouldn't lead to duplicated dependencies.
        $asset->withDependencies('foo');
        static::assertEquals(['foo', 'bar', 'baz'], $asset->dependencies());
    }

    /**
     * @test
     */
    public function testLocation(): void
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertSame(Asset::FRONTEND, $asset->location());

        $asset->forLocation(Asset::BACKEND);
        static::assertSame(Asset::BACKEND, $asset->location());
    }

    /**
     * @test
     */
    public function testFilters(): void
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertEmpty($asset->filters());

        $expectedFilter1 = static function (): string {
            return 'foo';
        };

        $expectedFilter2 = static function (): string {
            return 'bar';
        };

        $asset->withFilters($expectedFilter1, $expectedFilter2);

        static::assertEquals([$expectedFilter1, $expectedFilter2], $asset->filters());
    }

    /**
     * @test
     */
    public function testEnqueue()
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertTrue($asset->enqueue());

        $asset->canEnqueue(false);
        static::assertFalse($asset->enqueue());

        $asset->canEnqueue('__return_true');
        static::assertTrue($asset->enqueue());
    }

    /**
     * @test
     */
    public function testWithCondition()
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return '';
            }
        };

        static::assertEmpty($asset->data());

        $expected = bin2hex(random_bytes(4));

        $asset->withCondition($expected);
        static::assertSame(['conditional' => $expected], $asset->data());
    }

    /**
     * @test
     */
    public function testHandler()
    {
        $asset = new class ('', '') extends BaseAsset {
            protected function defaultHandler(): string
            {
                return __CLASS__;
            }
        };

        static::assertSame(get_class($asset), $asset->handler());

        $expected = bin2hex(random_bytes(4));
        $asset->useHandler($expected);
        static::assertSame($expected, $asset->handler());
    }
}
