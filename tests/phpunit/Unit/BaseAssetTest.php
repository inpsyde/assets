<?php # -*- coding: utf-8 -*-

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\BaseAsset;

class BaseAssetTest extends AbstractTestCase
{

    public function testBasic()
    {
        $testee = $this->getMockForAbstractClass(BaseAsset::class);

        static::assertInstanceOf(Asset::class, $testee);
        static::assertEmpty($testee->url());
        static::assertEmpty($testee->handle());
        static::assertEmpty($testee->version());
        static::assertTrue($testee->enqueue());
        static::assertEmpty($testee->filters());
        static::assertEmpty($testee->dependencies());
        static::assertEmpty($testee->data());
        static::assertSame(Asset::FRONTEND, $testee->location());
    }
}
