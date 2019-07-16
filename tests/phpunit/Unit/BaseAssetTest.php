<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\BaseAsset;

class BaseAssetTest extends AbstractTestCase
{

    public function testBasic()
    {
        $expectedHandle = 'expectedHandle';
        $expectedUrl = 'expectedUrl.js';

        /** @var BaseAsset $testee */
        $testee = $this->createTestee($expectedHandle, $expectedUrl);

        static::assertInstanceOf(Asset::class, $testee);
        static::assertSame($expectedUrl, $testee->url());
        static::assertSame($expectedHandle, $testee->handle());
        static::assertTrue($testee->enqueue());
        static::assertEmpty($testee->filters());
        static::assertEmpty($testee->data());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }

    public function testVersion()
    {
        /** @var BaseAsset $testee */
        $testee = $this->createTestee();

        // if automatic discovering of version is disabled and no version is set --> ''
        $testee->disableAutodiscoverVersion();
        static::assertSame('', $testee->version());

        $expectedFilePath = __DIR__.'/../../fixtures/style.css';
        $expected = (string) filemtime($expectedFilePath);
        $testee->enableAutodiscoverVersion();
        $testee->withFilePath($expectedFilePath);
        static::assertSame($expected, $testee->version());

        // if we set a version, the version should be returned.
        $testee->withVersion('foo');
        static::assertEquals('foo', $testee->version());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilePath()
    {
        $expectedFilePath = __DIR__.'/../../fixtures/style.css';

        $mockedAssetPathResolver = \Mockery::mock('alias:Inpsyde\Assets\AssetPathResolver');
        $mockedAssetPathResolver->shouldReceive('resolve')
            ->andReturn($expectedFilePath);

        $testee = $this->createTestee('foo', 'https://localhost.com/style.css');
        static::assertSame($expectedFilePath, $testee->filePath());
        static::assertSame($expectedFilePath, $testee->filePath());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFilePathFails()
    {
        $mockedAssetPathResolver = \Mockery::mock('alias:Inpsyde\Assets\AssetPathResolver');
        $mockedAssetPathResolver->shouldReceive('resolve')
            ->andReturn(null);

        $testee = $this->createTestee('foo', 'https://localhost.com/style.css');
        static::assertSame('', $testee->filePath());
    }

    public function testDependencies()
    {
        /** @var BaseAsset $testee */
        $testee = $this->createTestee();
        static::assertEmpty($testee->dependencies());

        $testee->withDependencies('foo');
        static::assertEquals(['foo'], $testee->dependencies());

        $testee->withDependencies('bar', 'baz');
        static::assertEquals(['foo', 'bar', 'baz'], $testee->dependencies());

        // Adding "foo" again shouldn't lead to duplicated dependencies.
        $testee->withDependencies('foo');
        static::assertEquals(['foo', 'bar', 'baz'], $testee->dependencies());
    }

    public function testLocation()
    {
        /** @var BaseAsset $testee */
        $testee = $this->createTestee();
        static::assertSame(Asset::FRONTEND, $testee->location());

        $testee->forLocation(Asset::BACKEND);
        static::assertSame(Asset::BACKEND, $testee->location());
    }

    public function testFilters()
    {
        /** @var BaseAsset $testee */
        $testee = $this->createTestee();
        static::assertEmpty($testee->filters());

        $expectedFilter1 = static function (): string {
            return 'foo';
        };
        $expectedFilter2 = static function (): string {
            return 'bar';
        };

        $testee->withFilters($expectedFilter1, $expectedFilter2);
        static::assertEquals([$expectedFilter1, $expectedFilter2], $testee->filters());
    }

    public function testEnqueue()
    {
        /** @var BaseAsset $testee */
        $testee = $this->createTestee();
        static::assertTrue($testee->enqueue());

        $testee->canEnqueue(false);
        static::assertFalse($testee->enqueue());

        $testee->canEnqueue(
            static function (): bool {
                return true;
            }
        );
        static::assertTrue($testee->enqueue());
    }

    public function testWithCondition()
    {
        $expected = 'foo';

        /** @var BaseAsset $testee */
        $testee = $this->createTestee();
        static::assertEmpty($testee->data());

        $testee->withCondition($expected);
        static::assertSame(['conditional' => $expected], $testee->data());
    }

    public function testHandler()
    {
        $expectedDefault = 'foo';
        $expected = 'bar';

        $testee = new class($expectedDefault) extends BaseAsset
        {

            private $expectedDefault;

            public function __construct($expectedDefault)
            {
                $this->expectedDefault = $expectedDefault;
            }

            protected function defaultHandler(): string
            {
                return $this->expectedDefault;
            }
        };
        static::assertSame('foo', $testee->handler());

        $testee->useHandler($expected);
        static::assertSame($expected, $testee->handler());
    }

    /**
     * @param string $handle
     * @param string $url
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|BaseAsset
     * @throws \InvalidArgumentException
     * @throws \PHPUnit\Framework\Exception
     * @throws \ReflectionException
     */
    private function createTestee(string $handle = 'foo', string $url = 'foo.js')
    {
        return $this->getMockForAbstractClass(BaseAsset::class, [$handle, $url]);
    }
}
