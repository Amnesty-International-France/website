<?php

declare(strict_types=1);

if (! function_exists('amnesty_image_compare_has_image')) {
    /**
     * Check whether a Jetpack Image Compare image attribute is usable.
     *
     * @param mixed $image Image attribute.
     *
     * @return bool
     */
    function amnesty_image_compare_has_image(mixed $image): bool
    {
        return is_array($image) && ! empty($image['url']);
    }
}

if (! function_exists('amnesty_image_compare_set_image_attributes')) {
    /**
     * Apply image attributes to the next image tag in a tag processor.
     *
     * @param WP_HTML_Tag_Processor $tags  HTML tag processor.
     * @param array<string,mixed>   $image Image data.
     *
     * @return void
     */
    function amnesty_image_compare_set_image_attributes(WP_HTML_Tag_Processor $tags, array $image): void
    {
        $attachment_id = ! empty($image['id']) ? absint($image['id']) : 0;

        $tags->remove_attribute('srcset');
        $tags->remove_attribute('sizes');

        if ($attachment_id) {
            $tags->set_attribute('id', (string) $attachment_id);

            if (function_exists('wp_get_attachment_image_srcset')) {
                $srcset = wp_get_attachment_image_srcset($attachment_id, 'full');

                if ($srcset) {
                    $tags->set_attribute('srcset', $srcset);
                }
            }

            if (function_exists('wp_get_attachment_image_sizes')) {
                $sizes = wp_get_attachment_image_sizes($attachment_id, 'full');

                if ($sizes) {
                    $tags->set_attribute('sizes', $sizes);
                }
            }
        } else {
            $tags->remove_attribute('id');
        }

        $tags->set_attribute('src', esc_url((string) $image['url']));
        $tags->set_attribute('alt', (string) ($image['alt'] ?? ''));
        amnesty_image_compare_set_image_class($tags, $attachment_id);

        if (! empty($image['width'])) {
            $tags->set_attribute('width', (string) absint($image['width']));
        } else {
            $tags->remove_attribute('width');
        }

        if (! empty($image['height'])) {
            $tags->set_attribute('height', (string) absint($image['height']));
        } else {
            $tags->remove_attribute('height');
        }
    }
}

if (! function_exists('amnesty_image_compare_set_image_class')) {
    /**
     * Replace the WordPress attachment class on an image without keeping a stale desktop ID.
     *
     * @param WP_HTML_Tag_Processor $tags          HTML tag processor.
     * @param int                   $attachment_id Attachment ID.
     *
     * @return void
     */
    function amnesty_image_compare_set_image_class(WP_HTML_Tag_Processor $tags, int $attachment_id): void
    {
        $class = $tags->get_attribute('class');
        $classes = is_string($class) ? preg_split('/\s+/', trim($class)) : [];
        $classes = array_values(array_filter(
            $classes ?: [],
            static fn (string $class_name): bool => '' !== $class_name && ! preg_match('/^wp-image-\d+$/', $class_name)
        ));

        if ($attachment_id) {
            $classes[] = sprintf('wp-image-%d', $attachment_id);
        }

        $classes = array_values(array_unique($classes));

        if ($classes) {
            $tags->set_attribute('class', implode(' ', $classes));
            return;
        }

        $tags->remove_attribute('class');
    }
}

if (! function_exists('amnesty_image_compare_content_with_images')) {
    /**
     * Replace the before/after image tags inside Jetpack Image Compare content.
     *
     * @param string              $content HTML content.
     * @param array<string,mixed> $before  Before image.
     * @param array<string,mixed> $after   After image.
     *
     * @return string
     */
    function amnesty_image_compare_content_with_images(string $content, array $before, array $after): string
    {
        $tags = new WP_HTML_Tag_Processor($content);
        $images = [$before, $after];
        $index = 0;

        while ($index < 2 && $tags->next_tag('img')) {
            amnesty_image_compare_set_image_attributes($tags, $images[$index]);
            ++$index;
        }

        return $tags->get_updated_html();
    }
}

if (! function_exists('amnesty_image_compare_classes')) {
    /**
     * Build a unique, trimmed class attribute value.
     *
     * @param string ...$class_groups Class name groups.
     *
     * @return string
     */
    function amnesty_image_compare_classes(string ...$class_groups): string
    {
        $classes = [];

        foreach ($class_groups as $class_group) {
            foreach (preg_split('/\s+/', trim($class_group)) ?: [] as $class_name) {
                if ('' !== $class_name) {
                    $classes[] = $class_name;
                }
            }
        }

        return implode(' ', array_unique($classes));
    }
}

if (! function_exists('amnesty_image_compare_root_context')) {
    /**
     * Read the public root tag context from existing block content.
     *
     * @param string              $content    HTML content.
     * @param array<string,mixed> $attributes Block attributes.
     *
     * @return array{tag:string,class:string,style:string}
     */
    function amnesty_image_compare_root_context(string $content, array $attributes): array
    {
        $fallback_classes = amnesty_image_compare_classes(
            'wp-block-jetpack-image-compare',
            ! empty($attributes['align']) ? sprintf('align%s', $attributes['align']) : '',
            (string) ($attributes['className'] ?? '')
        );

        $context = [
            'tag' => 'figure',
            'class' => $fallback_classes,
            'style' => '',
        ];

        $tags = new WP_HTML_Tag_Processor($content);

        if (! $tags->next_tag()) {
            return $context;
        }

        $class = $tags->get_attribute('class');
        $class = is_string($class) ? $class : '';

        if (str_contains($class, 'juxtapose')) {
            return $context;
        }

        $tag = $tags->get_tag();

        if (is_string($tag) && preg_match('/^[a-z0-9-]+$/i', $tag)) {
            $context['tag'] = strtolower($tag);
        }

        if ('' !== trim($class)) {
            $context['class'] = $class;
        }

        $style = $tags->get_attribute('style');

        if (is_string($style)) {
            $context['style'] = $style;
        }

        return $context;
    }
}

if (! function_exists('amnesty_image_compare_strip_root_public_attributes')) {
    /**
     * Remove public root layout attributes from a cloned block before nesting it.
     *
     * @param string $content HTML content.
     *
     * @return string
     */
    function amnesty_image_compare_strip_root_public_attributes(string $content): string
    {
        $tags = new WP_HTML_Tag_Processor($content);

        if (! $tags->next_tag()) {
            return $content;
        }

        $class = $tags->get_attribute('class');

        if (is_string($class) && str_contains($class, 'juxtapose')) {
            return $content;
        }

        $tags->remove_attribute('class');
        $tags->remove_attribute('style');

        return $tags->get_updated_html();
    }
}

if (! function_exists('amnesty_image_compare_render_image')) {
    /**
     * Render a fallback image tag for the Jetpack Image Compare bootstrap script.
     *
     * @param array<string,mixed> $image Image data.
     *
     * @return string
     */
    function amnesty_image_compare_render_image(array $image): string
    {
        $attachment_id = ! empty($image['id']) ? absint($image['id']) : 0;
        $attributes = [
            'src' => (string) $image['url'],
            'alt' => (string) ($image['alt'] ?? ''),
        ];

        if ($attachment_id) {
            $attributes['id'] = (string) $attachment_id;
            $attributes['class'] = sprintf('wp-image-%d', $attachment_id);
        }

        if (! empty($image['width'])) {
            $attributes['width'] = (string) absint($image['width']);
        }

        if (! empty($image['height'])) {
            $attributes['height'] = (string) absint($image['height']);
        }

        $html_attributes = '';

        foreach ($attributes as $name => $value) {
            $html_attributes .= sprintf(
                ' %s="%s"',
                esc_attr($name),
                'src' === $name ? esc_url($value) : esc_attr($value)
            );
        }

        return sprintf('<img%s />', $html_attributes);
    }
}

if (! function_exists('amnesty_image_compare_render_fallback')) {
    /**
     * Render minimal Jetpack-compatible markup when the desktop content has no image tags.
     *
     * @param array<string,mixed> $before     Before image.
     * @param array<string,mixed> $after      After image.
     * @param array<string,mixed> $attributes Block attributes.
     * @param string              $content    Original block content.
     *
     * @return string
     */
    function amnesty_image_compare_render_fallback(array $before, array $after, array $attributes, string $content = ''): string
    {
        $root = amnesty_image_compare_root_context($content, $attributes);
        $orientation = (string) ($attributes['orientation'] ?? 'horizontal');
        $caption = (string) ($attributes['caption'] ?? '');
        $style = '' !== $root['style'] ? sprintf(' style="%s"', esc_attr($root['style'])) : '';
        $caption_html = '' !== $caption ? sprintf('<figcaption>%s</figcaption>', wp_kses_post($caption)) : '';

        return sprintf(
            '<%1$s class="%2$s"%3$s><div class="juxtapose" data-mode="%4$s">%5$s%6$s</div>%7$s</%1$s>',
            $root['tag'],
            esc_attr($root['class']),
            $style,
            esc_attr($orientation),
            amnesty_image_compare_render_image($before),
            amnesty_image_compare_render_image($after),
            $caption_html
        );
    }
}

if (! function_exists('amnesty_image_compare_has_two_images')) {
    /**
     * Check whether rendered content has enough images for Jetpack's compare script.
     *
     * @param string $content HTML content.
     *
     * @return bool
     */
    function amnesty_image_compare_has_two_images(string $content): bool
    {
        $tags = new WP_HTML_Tag_Processor($content);
        $count = 0;

        while ($count < 2 && $tags->next_tag('img')) {
            ++$count;
        }

        return 2 === $count;
    }
}

if (! function_exists('amnesty_image_compare_images_match')) {
    /**
     * Check whether two image attributes point to the same media.
     *
     * @param array<string,mixed> $first  First image.
     * @param array<string,mixed> $second Second image.
     *
     * @return bool
     */
    function amnesty_image_compare_images_match(array $first, array $second): bool
    {
        $first_id = ! empty($first['id']) ? absint($first['id']) : 0;
        $second_id = ! empty($second['id']) ? absint($second['id']) : 0;

        if ($first_id && $second_id) {
            return $first_id === $second_id;
        }

        return (string) ($first['url'] ?? '') === (string) ($second['url'] ?? '');
    }
}

if (! function_exists('amnesty_add_mobile_images_to_image_compare_block')) {
    /**
     * Add mobile variants to the Jetpack Image Compare block rendering.
     *
     * @param string $content The block content.
     * @param array  $block   The parsed block.
     *
     * @return string
     */
    function amnesty_add_mobile_images_to_image_compare_block(string $content, array $block): string
    {
        if ('jetpack/image-compare' !== ($block['blockName'] ?? null)) {
            return $content;
        }

        $attributes = $block['attrs'] ?? [];
        $desktop_before = $attributes['imageBefore'] ?? [];
        $desktop_after = $attributes['imageAfter'] ?? [];
        $mobile_before = $attributes['imageBeforeMobile'] ?? [];
        $mobile_after = $attributes['imageAfterMobile'] ?? [];

        if (! amnesty_image_compare_has_image($mobile_before) && ! amnesty_image_compare_has_image($mobile_after)) {
            return $content;
        }

        $has_desktop_pair = amnesty_image_compare_has_image($desktop_before) && amnesty_image_compare_has_image($desktop_after);
        $has_mobile_pair = amnesty_image_compare_has_image($mobile_before) && amnesty_image_compare_has_image($mobile_after);

        if (! $has_desktop_pair && ! $has_mobile_pair) {
            return $content;
        }

        if (! $has_desktop_pair) {
            $mobile_only_content = amnesty_image_compare_content_with_images($content, $mobile_before, $mobile_after);

            return amnesty_image_compare_has_two_images($mobile_only_content)
                ? $mobile_only_content
                : amnesty_image_compare_render_fallback($mobile_before, $mobile_after, $attributes, $content);
        }

        $resolved_mobile_before = amnesty_image_compare_has_image($mobile_before) ? $mobile_before : $desktop_before;
        $resolved_mobile_after = amnesty_image_compare_has_image($mobile_after) ? $mobile_after : $desktop_after;

        if (
            amnesty_image_compare_images_match($desktop_before, $resolved_mobile_before)
            && amnesty_image_compare_images_match($desktop_after, $resolved_mobile_after)
        ) {
            return $content;
        }

        $mobile_content = amnesty_image_compare_content_with_images($content, $resolved_mobile_before, $resolved_mobile_after);
        $root = amnesty_image_compare_root_context($content, $attributes);
        $root['class'] = amnesty_image_compare_classes($root['class'], 'amnesty-image-compare-responsive');
        $style = '' !== $root['style'] ? sprintf(' style="%s"', esc_attr($root['style'])) : '';

        return sprintf(
            '<%1$s class="%2$s"%3$s><div class="amnesty-image-compare-desktop">%4$s</div><div class="amnesty-image-compare-mobile">%5$s</div></%1$s>',
            $root['tag'],
            esc_attr($root['class']),
            $style,
            amnesty_image_compare_strip_root_public_attributes($content),
            amnesty_image_compare_strip_root_public_attributes($mobile_content)
        );
    }
}

add_filter('render_block', 'amnesty_add_mobile_images_to_image_compare_block', 110, 2);
