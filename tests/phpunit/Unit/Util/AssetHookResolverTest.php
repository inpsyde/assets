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

namespace Inpsyde\Assets\Tests\Unit\Util;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Util\AssetHookResolver;
use Inpsyde\Assets\Tests\Unit\AbstractTestCase;
use Inpsyde\WpContext;

class AssetHookResolverTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testResolveNothingWhenNotNeeded(): void
    {
        $noAssetsContexts = [
            WpContext::AJAX,
            WpContext::CLI,
            WpContext::CRON,
            WpContext::INSTALLING,
            WpContext::REST,
            WpContext::XML_RPC,
        ];

        foreach ($noAssetsContexts as $noAssetsContext) {
            $context = WpContext::new()->force($noAssetsContext);
            $hookResolver = new AssetHookResolver($context);

            static::assertSame([], $hookResolver->resolve());

            $hookResolver = new AssetHookResolver($context->withCli());

            static::assertSame([], $hookResolver->resolve());
        }
    }

    /**
     * @test
     */
    public function testResolveLogin(): void
    {
        $context = WpContext::new()->force(WpContext::LOGIN);
        $hookResolver = new AssetHookResolver($context);

        static::assertSame([Asset::HOOK_LOGIN], $hookResolver->resolve());
    }

    /**
     * @test
     */
    public function testResolveFrontend(): void
    {
        $context = WpContext::new()->force(WpContext::FRONTOFFICE);
        $hookResolver = new AssetHookResolver($context);

        static::assertSame(
            [Asset::HOOK_BLOCK_ASSETS, Asset::HOOK_FRONTEND, Asset::HOOK_CUSTOMIZER_PREVIEW],
            $hookResolver->resolve()
        );
    }

    /**
     * @test
     */
    public function testResolveBackend(): void
    {
        $context = WpContext::new()->force(WpContext::BACKOFFICE);
        $hookResolver = new AssetHookResolver($context);

        static::assertSame(
            [
                Asset::HOOK_BLOCK_ASSETS,
                Asset::HOOK_BLOCK_EDITOR_ASSETS,
                Asset::HOOK_CUSTOMIZER,
                Asset::HOOK_BACKEND,
            ],
            $hookResolver->resolve()
        );
    }
}
