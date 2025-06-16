<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\BaseAsset;
use Inpsyde\Assets\DataAwareAsset;
use Inpsyde\Assets\DataAwareTrait;

class DataAwareTraitTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testWithCondition(): void
    {
        $asset = $this->createDataAwareAsset();
        static::assertEmpty($asset->data());

        $expected = bin2hex(random_bytes(4));

        $asset->withCondition($expected);
        static::assertSame(['conditional' => $expected], $asset->data());
    }

    /**
     * @test
     */
    public function testWithData(): void
    {
        $asset = $this->createDataAwareAsset();
        static::assertEmpty($asset->data());

        $expectedData = ['key' => 'value'];
        $asset->withData($expectedData);

        static::assertSame($expectedData, $asset->data());
    }

    /**
     * @test
     */
    public function testWithDataMerging(): void
    {
        $asset = $this->createDataAwareAsset();
        
        $firstData = ['key1' => 'value1'];
        $secondData = ['key2' => 'value2'];
        
        $asset->withData($firstData);
        $asset->withData($secondData);

        $expected = ['key1' => 'value1', 'key2' => 'value2'];
        static::assertSame($expected, $asset->data());
    }

    private function createDataAwareAsset(string $handle = '', string $src = ''): DataAwareAsset
    {
        return new class ($handle, $src) extends BaseAsset implements DataAwareAsset {
            use DataAwareTrait;

            protected function defaultHandler(): string
            {
                return __CLASS__;
            }
        };
    }
}