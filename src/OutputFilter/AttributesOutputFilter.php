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

class AttributesOutputFilter implements AssetOutputFilter
{
    public function __invoke(string $html, Asset $asset): string
    {
        $attributes = $asset->attributes();
        if (count($attributes) === 0) {
            return $html;
        }

        if (!class_exists(\WP_HTML_Tag_Processor::class)) {
            // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
            trigger_error(
                'Adding attributes is not supported for WordPress < 6.2',
                \E_USER_DEPRECATED
            );
            // phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_trigger_error

            return $html;
        }

        $tags = new \WP_HTML_Tag_Processor($html);

        // Only extend <script> elements with "src" attribute
        // and don't extend inline <script></script> before and after.
        if (
            $tags->next_tag(['tag_name' => 'script'])
            && (string) $tags->get_attribute('src')
        ) {
            $this->applyAttributes($tags, $attributes);
        }

        return $tags->get_updated_html();
    }

    protected function applyAttributes(\WP_HTML_Tag_Processor $script, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $key = esc_attr((string)$key);
            if ((string) $script->get_attribute($key)) {
                continue;
            }
            if (is_bool($value) && !$value) {
                continue;
            }
            $value = is_bool($value)
                ? esc_attr($key)
                : esc_attr((string)$value);

            $script->set_attribute($key, $value);
        }
    }
}
