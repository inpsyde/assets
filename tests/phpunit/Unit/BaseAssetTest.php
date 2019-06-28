<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\BaseAsset;

class BaseAssetTest extends AbstractTestCase
{

    public function testBasic()
    {
        /** @var BaseAsset $testee */
        $testee = $this->getMockForAbstractClass(BaseAsset::class);

        static::assertInstanceOf(Asset::class, $testee);
        static::assertEmpty($testee->url());
        static::assertEmpty($testee->handle());
        static::assertEmpty($testee->version());
        static::assertTrue($testee->enqueue());
        static::assertEmpty($testee->filters());
        static::assertEmpty($testee->data());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }

    public function testVersion()
    {
        /** @var BaseAsset $testee */
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
        static::assertSame('', $testee->version());

        $testee->withVersion('foo');
        static::assertEquals('foo', $testee->version());
    }

    public function testDependencies()
    {
        /** @var BaseAsset $testee */
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
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
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
        static::assertSame(Asset::FRONTEND, $testee->location());

        $testee->forLocation(Asset::BACKEND);
        static::assertSame(ASset::BACKEND, $testee->location());
    }

    public function testFilters()
    {
        /** @var BaseAsset $testee */
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
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
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
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
        $testee = $this->getMockForAbstractClass(BaseAsset::class);
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
}
