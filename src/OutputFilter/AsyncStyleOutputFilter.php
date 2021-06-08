<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\Asset;

class AsyncStyleOutputFilter implements AssetOutputFilter
{
    /**
     * @var string
     * phpcs:disable Inpsyde.CodeQuality.LineLength
     */
    private $polyfill = '!function(t){"use strict";t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){var e=t.media||"all";function a(){t.media=e}t.addEventListener?t.addEventListener("load",a):t.attachEvent&&t.attachEvent("onload",a),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(a,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS}("undefined"!=typeof global?global:this);';

    /**
     * @var bool
     * phpcs:enable Inpsyde.CodeQuality.LineLength
     */
    private $polyfillPrinted = false;

    /**
     * @param string $html
     * @param Asset $asset
     *
     * @return string
     */
    public function __invoke(string $html, Asset $asset): string
    {
        $url = $asset->url();
        $version = $asset->version();
        if ($version) {
            $url = add_query_arg('ver', $version, $url);
        }

        ob_start();
        ?>
        <link rel="preload" href="%s" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>%s</noscript>
        <?php
        $format = ob_get_clean();

        $output = sprintf((string) $format, esc_url($url), $html);

        if (!$this->polyfillPrinted) {
            $output .= "<script>{$this->polyfill}</script>";
            $this->polyfillPrinted = true;
        }

        return $output;
    }
}
