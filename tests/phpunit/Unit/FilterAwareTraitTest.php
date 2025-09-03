<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\BaseAsset;
use Inpsyde\Assets\FilterAwareAsset;
use Inpsyde\Assets\FilterAwareTrait;
use Inpsyde\Assets\OutputFilter\AttributesOutputFilter;
use Inpsyde\Assets\OutputFilter\InlineAssetOutputFilter;

class FilterAwareTraitTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testFilters(): void
    {
        $asset = $this->createFilterAwareAsset();

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
    public function testUseInlineFilter(): void
    {
        $asset = $this->createFilterAwareAsset();
        $asset->useInlineFilter();

        $filters = $asset->filters();

        static::assertSame(InlineAssetOutputFilter::class, $filters[0]);
    }

    /**
     * @test
     */
    public function testAttributes(): void
    {
        $expectedAttributes = ['foo' => 'bar'];

        $asset = $this->createFilterAwareAsset();
        $asset->withAttributes($expectedAttributes);

        static::assertSame($expectedAttributes, $asset->attributes());

        $filters = $asset->filters();
        static::assertSame(AttributesOutputFilter::class, $filters[0]);
    }

    /**
     * @test
     */
    public function testAttributesAddedMultipleTimes(): void
    {
        $expectedValue = 'baz';
        $expectedAttributes1 = [
            'foo' => 'foo',
        ];
        $expectedAttributes2 = [
            'bar' => 'bar',
            // overwrite "foo"
            'foo' => $expectedValue,
        ];

        $asset = $this->createFilterAwareAsset();
        $asset->withAttributes($expectedAttributes1);
        $asset->withAttributes($expectedAttributes2);

        $attributes = $asset->attributes();

        static::assertArrayHasKey('foo', $attributes);
        static::assertArrayHasKey('bar', $attributes);
        static::assertSame($expectedValue, $attributes['foo']);
    }

    private function createFilterAwareAsset(string $handle = '', string $src = ''): FilterAwareAsset
    {
        return new class ($handle, $src) extends BaseAsset implements FilterAwareAsset {
            use FilterAwareTrait;

            protected function defaultHandler(): string
            {
                return __CLASS__;
            }
        };
    }
}