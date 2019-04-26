<?php

namespace Inpsyde\Assets\Tests\Unit;

use Brain\Monkey;
use Inpsyde\Assets\Asset;
use Inpsyde\Assets\AssetHookResolver;

class AssetHookResolverTest extends AbstractTestCase
{

    public function testResolveBackend()
    {
        ! defined('ABSPATH') and define('ABSPATH', __DIR__);
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(false);
        Monkey\Functions\expect('wp_doing_cron')->andReturn(false);

        $testee = new AssetHookResolver();;
        static::assertSame([Asset::HOOK_BACKEND], $testee->resolve());
    }

    public function testResolveAjax()
    {
        ! defined('ABSPATH') and define('ABSPATH', __DIR__);
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(true);
        Monkey\Functions\expect('wp_doing_cron')->andReturn(false);

        $testee = new AssetHookResolver();;
        static::assertSame([], $testee->resolve());
    }

    public function testResolveLogin()
    {
        ! defined('ABSPATH') and define('ABSPATH', __DIR__);
        Monkey\Functions\expect('is_admin')->andReturn(false);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(false);
        Monkey\Functions\expect('wp_doing_cron')->andReturn(false);

        $cur = $GLOBALS['pagenow'] ?? '';
        $GLOBALS['pagenow'] = 'wp-login.php';

        $testee = new AssetHookResolver();;
        static::assertSame([Asset::HOOK_LOGIN], $testee->resolve());

        // restor global var if exist.
        $GLOBALS['pagenow'] = $cur;
    }

    public function testResolvePostEditOrNewWithGutenberg()
    {
        ! defined('ABSPATH') and define('ABSPATH', __DIR__);
        Monkey\Functions\expect('is_admin')->andReturn(true);
        Monkey\Functions\expect('is_customize_preview')->andReturn(false);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(false);
        Monkey\Functions\expect('wp_doing_cron')->andReturn(false);

        $cur = $GLOBALS['pagenow'] ?? '';
        $GLOBALS['pagenow'] = 'post.php';

        $testee = new AssetHookResolver();
        $result = $testee->resolve();

        static::assertCount(2, $result);
        static::assertContains(Asset::HOOK_BACKEND, $result);
        static::assertContains(Asset::HOOK_BLOCK_EDITOR_ASSETS, $result);

        // restor global var if exist.
        $GLOBALS['pagenow'] = $cur;
    }

    public function testResolveCustomizer()
    {
        ! defined('ABSPATH') and define('ABSPATH', __DIR__);
        Monkey\Functions\expect('is_admin')->andReturn(false);
        Monkey\Functions\expect('is_customize_preview')->andReturn(true);
        Monkey\Functions\expect('wp_doing_ajax')->andReturn(false);
        Monkey\Functions\expect('wp_doing_cron')->andReturn(false);

        $testee = new AssetHookResolver();
        $result = $testee->resolve();

        static::assertCount(2, $result);
        static::assertContains(Asset::HOOK_FRONTEND, $result);
        static::assertContains(Asset::HOOK_CUSTOMIZER, $result);
    }
}
