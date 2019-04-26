<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Assets;

class AssetHookResolver
{

    /**
     * Resolving to the current location/page in WordPress all current Hooks
     *
     * @return array
     */
    public function resolve(): array
    {
        $pageNow = $GLOBALS['pagenow'] ?? '';
        $pageNow = basename($pageNow);

        $isCore = defined('ABSPATH');
        $isAjax = $isCore
            ? wp_doing_ajax()
            : false;
        $isAdmin = $isCore
            ? is_admin() && ! $isAjax
            : false;
        $isCron = $isCore
            ? wp_doing_cron()
            : false;
        $isLogin = ($pageNow === 'wp-login.php');
        $isPostEdit = ($pageNow === 'post.php') || ($pageNow === 'post-new.php');
        $isCli = defined('WP_CLI');
        $isFront = ! $isAdmin && ! $isAjax && ! $isCron && ! $isLogin && ! $isCli;
        $isCustomizer = is_customize_preview();

        $hooks = [];

        if ($isAjax) {
            return [];
        }

        if ($isLogin) {
            $hooks[] = Asset::HOOK_LOGIN;
        }

        if ($isPostEdit) {
            $hooks[] = Asset::HOOK_BLOCK_EDITOR_ASSETS;
        }

        if ($isFront) {
            $hooks[] = Asset::HOOK_FRONTEND;
        }

        if ($isCustomizer) {
            $hooks[] = Asset::HOOK_CUSTOMIZER;
        }

        if ($isAdmin) {
            $hooks[] = Asset::HOOK_BACKEND;
        }

        return $hooks;
    }
}
