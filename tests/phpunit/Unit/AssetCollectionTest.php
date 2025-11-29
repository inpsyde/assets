<?php

declare(strict_types=1);

namespace Inpsyde\Assets\Tests\Unit;

use Inpsyde\Assets\AssetCollection;
use Inpsyde\Assets\Script;
use Inpsyde\Assets\Style;

class AssetCollectionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testAddAndGet(): void
    {
        $collection = new AssetCollection();

        $script = new Script('my-script', 'script.js');
        $style = new Style('my-style', 'style.css');

        $collection->add($script);
        $collection->add($style);

        static::assertSame($script, $collection->get('my-script', Script::class));
        static::assertSame($style, $collection->get('my-style', Style::class));
    }

    /**
     * @test
     */
    public function testAllReturnsSortedByPriority(): void
    {
        $collection = new AssetCollection();

        $scriptHigh = new Script('script-high', 'high.js');
        $scriptHigh->withPriority(20);

        $scriptLow = new Script('script-low', 'low.js');
        $scriptLow->withPriority(5);

        $scriptDefault = new Script('script-default', 'default.js');
        // Default priority is 10

        // Add in non-priority order
        $collection->add($scriptHigh);
        $collection->add($scriptDefault);
        $collection->add($scriptLow);

        $all = $collection->all();
        $scripts = array_values($all[Script::class]);

        // Should be sorted: low (5), default (10), high (20)
        static::assertSame('script-low', $scripts[0]->handle());
        static::assertSame('script-default', $scripts[1]->handle());
        static::assertSame('script-high', $scripts[2]->handle());
    }

    /**
     * @test
     */
    public function testAllSortsEachTypeSeparately(): void
    {
        $collection = new AssetCollection();

        $scriptA = new Script('script-a', 'a.js');
        $scriptA->withPriority(20);

        $scriptB = new Script('script-b', 'b.js');
        $scriptB->withPriority(5);

        $styleA = new Style('style-a', 'a.css');
        $styleA->withPriority(15);

        $styleB = new Style('style-b', 'b.css');
        $styleB->withPriority(1);

        $collection->add($scriptA);
        $collection->add($styleA);
        $collection->add($scriptB);
        $collection->add($styleB);

        $all = $collection->all();

        $scripts = array_values($all[Script::class]);
        $styles = array_values($all[Style::class]);

        // Scripts sorted: b (5), a (20)
        static::assertSame('script-b', $scripts[0]->handle());
        static::assertSame('script-a', $scripts[1]->handle());

        // Styles sorted: b (1), a (15)
        static::assertSame('style-b', $styles[0]->handle());
        static::assertSame('style-a', $styles[1]->handle());
    }

    /**
     * @test
     */
    public function testHas(): void
    {
        $collection = new AssetCollection();

        $script = new Script('my-script', 'script.js');
        $collection->add($script);

        static::assertTrue($collection->has('my-script', Script::class));
        static::assertFalse($collection->has('my-script', Style::class));
        static::assertFalse($collection->has('other', Script::class));
    }

    /**
     * @test
     */
    public function testGetFirst(): void
    {
        $collection = new AssetCollection();

        $script = new Script('my-handle', 'script.js');
        $style = new Style('my-handle', 'style.css');

        $collection->add($script);
        $collection->add($style);

        // getFirst returns first match regardless of type
        $first = $collection->getFirst('my-handle');
        static::assertNotNull($first);
        static::assertSame('my-handle', $first->handle());
    }
}
