<?php

declare(strict_types=1);

namespace Inpsyde\Assets\OutputFilter;

use Inpsyde\Assets\FilterAwareAsset;

class AttributesOutputFilter implements AssetOutputFilter
{
    public function __invoke(string $html, FilterAwareAsset $asset): string
    {
        $attributes = $asset->attributes();
        if (!class_exists(\WP_HTML_Tag_Processor::class) || count($attributes) === 0) {
            return $html;
        }

        $tags = new \WP_HTML_Tag_Processor($html);

        // Only extend <script> elements with "src" attribute
        // and don't extend inline <script></script> before and after.
        while ($tags->next_tag(['tag_name' => 'script'])) {
            if ((string) $tags->get_attribute('src')) {
                $this->applyAttributes($tags, $attributes);
                break;
            }
        }

        return $tags->get_updated_html();
    }

    /**
     * @param \WP_HTML_Tag_Processor $script
     * @param array<string, string|bool> $attributes
     *
     * @return void
     */
    protected function applyAttributes(\WP_HTML_Tag_Processor $script, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $key = esc_attr((string) $key);
            if ((string) $script->get_attribute($key)) {
                continue;
            }
            if (is_bool($value) && !$value) {
                continue;
            }
            $value = is_bool($value)
                ? esc_attr($key)
                : esc_attr((string) $value);

            $script->set_attribute($key, $value);
        }
    }
}
