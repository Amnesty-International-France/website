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
        if (! empty($image['id'])) {
            $tags->set_attribute('id', (string) absint($image['id']));
        }

        $tags->set_attribute('src', (string) $image['url']);
        $tags->set_attribute('alt', (string) ($image['alt'] ?? ''));

        if (! empty($image['width'])) {
            $tags->set_attribute('width', (string) absint($image['width']));
        }

        if (! empty($image['height'])) {
            $tags->set_attribute('height', (string) absint($image['height']));
        }
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

        if (! amnesty_image_compare_has_image($desktop_before) || ! amnesty_image_compare_has_image($desktop_after)) {
            return $content;
        }

        if (! amnesty_image_compare_has_image($mobile_before) && ! amnesty_image_compare_has_image($mobile_after)) {
            return $content;
        }

        $mobile_content = amnesty_image_compare_content_with_images(
            $content,
            amnesty_image_compare_has_image($mobile_before) ? $mobile_before : $desktop_before,
            amnesty_image_compare_has_image($mobile_after) ? $mobile_after : $desktop_after
        );

        return sprintf(
            '<div class="amnesty-image-compare-responsive"><div class="amnesty-image-compare-desktop">%1$s</div><div class="amnesty-image-compare-mobile">%2$s</div></div>',
            $content,
            $mobile_content
        );
    }
}

add_filter('render_block', 'amnesty_add_mobile_images_to_image_compare_block', 110, 2);
