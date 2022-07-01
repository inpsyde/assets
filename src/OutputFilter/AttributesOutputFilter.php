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

/**
 * @psalm-suppress UndefinedMethod
 */
class AttributesOutputFilter implements AssetOutputFilter
{
    private const ROOT_ELEMENT_START = '<root>';
    private const ROOT_ELEMENT_END = '</root>';

    /**
     * @param string $html
     * @param Asset $asset
     *
     * @return string
     *
     * phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
     * @psalm-suppress PossiblyFalseArgument
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function __invoke(string $html, Asset $asset): string
    {
        $attributes = $asset->attributes();
        if (count($attributes) === 0) {
            return $html;
        }

        $html = $this->wrapHtmlIntoRoot($html);

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$doc->loadHTML(
            mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $scripts = $doc->getElementsByTagName('script');
        foreach ($scripts as $script) {
            // Only extend <script> elements with "src" attribute
            // and don't extend inline <script></script> before and after.
            if (!$script->hasAttribute('src')) {
                continue;
            }
            $this->applyAttributes($script, $attributes);
        }

        return $this->removeRootElement($doc->saveHTML());
    }

    /**
     * Wrapping multiple scripts into a root-element
     * to be able to load it via DOMDocument.
     *
     * @param string $html
     *
     * @return string
     */
    protected function wrapHtmlIntoRoot(string $html): string
    {
        return self::ROOT_ELEMENT_START . $html . self::ROOT_ELEMENT_END;
    }

    /**
     * Remove root element and return original HTML.
     *
     * @param string $html
     *
     * @return string
     * @see AttributesOutputFilter::wrapHtmlIntoRoot()
     *
     */
    protected function removeRootElement(string $html): string
    {
        $regex = '~' . self::ROOT_ELEMENT_START . '(.+?)' . self::ROOT_ELEMENT_END . '~s';
        preg_match($regex, $html, $matches);

        return $matches[1];
    }

    /**
     * @param \DOMElement $script
     * @param array $attributes
     *
     * @return void
     */
    protected function applyAttributes(\DOMElement $script, array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $key = esc_attr((string) $key);
            if ($script->hasAttribute($key)) {
                continue;
            }
            if (is_bool($value) && !$value) {
                continue;
            }
            $value = is_bool($value)
                ? esc_attr($key)
                : esc_attr((string) $value);

            $script->setAttribute($key, $value);
        }
    }
}
