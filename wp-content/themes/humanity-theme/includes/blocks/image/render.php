<?php

declare(strict_types=1);

if (!function_exists('amnesty_image_block_source')) {
    /**
     * Render a source tag for the responsive Image block picture.
     *
     * @param int          $attachment_id Attachment ID.
     * @param string|array $size          Image size.
     * @param string       $media         Media query.
     *
     * @return string
     */
    function amnesty_image_block_source(int $attachment_id, string|array $size, string $media): string
    {
        $srcset = function_exists('wp_get_attachment_image_srcset')
            ? (string) wp_get_attachment_image_srcset($attachment_id, $size)
            : '';

        if ('' === $srcset && function_exists('wp_get_attachment_image_src')) {
            $src = wp_get_attachment_image_src($attachment_id, $size);
            $srcset = is_array($src) ? (string) ($src[0] ?? '') : '';
        }

        if ('' === $srcset) {
            return '';
        }

        $sizes = function_exists('wp_get_attachment_image_sizes')
            ? (string) wp_get_attachment_image_sizes($attachment_id, $size)
            : '';
        $sizes_attribute = '' !== $sizes ? sprintf(' sizes="%s"', esc_attr($sizes)) : '';

        return sprintf(
            '<source media="%s" srcset="%s"%s />',
            esc_attr($media),
            esc_attr($srcset),
            $sizes_attribute
        );
    }
}

if (!function_exists('amnesty_image_block_tag_attribute')) {
    /**
     * Read an attribute from a single HTML tag.
     *
     * @param string $tag       HTML tag.
     * @param string $attribute Attribute name.
     *
     * @return string
     */
    function amnesty_image_block_tag_attribute(string $tag, string $attribute): string
    {
        if (preg_match('/\s' . preg_quote($attribute, '/') . '\s*=\s*(["\'])(.*?)\1/i', $tag, $matches)) {
            return html_entity_decode($matches[2], ENT_QUOTES);
        }

        return '';
    }
}

if (!function_exists('amnesty_image_block_attachment_img')) {
    /**
     * Render an attachment through WordPress and return its img tag.
     *
     * @param int                  $attachment_id Attachment ID.
     * @param string|array         $size          Image size.
     * @param array<string, mixed> $attr          Image attributes.
     *
     * @return string
     */
    function amnesty_image_block_attachment_img(int $attachment_id, string|array $size, array $attr = []): string
    {
        $html = wp_get_attachment_image($attachment_id, $size, false, $attr);

        if (preg_match('/<img\b[^>]*>/i', $html, $match)) {
            return $match[0];
        }

        return amnesty_image_block_img($attachment_id, $size, $attr);
    }
}

if (!function_exists('amnesty_image_block_source_from_img')) {
    /**
     * Build a desktop media source from the img emitted by WordPress.
     *
     * @param string $img   Image tag.
     * @param string $media Media query.
     *
     * @return string
     */
    function amnesty_image_block_source_from_img(string $img, string $media): string
    {
        $srcset = amnesty_image_block_tag_attribute($img, 'srcset') ?: amnesty_image_block_tag_attribute($img, 'src');

        if ('' === $srcset) {
            return '';
        }

        $sizes = amnesty_image_block_tag_attribute($img, 'sizes');
        $sizes_attribute = '' !== $sizes ? sprintf(' sizes="%s"', esc_attr($sizes)) : '';

        return sprintf(
            '<source media="%s" srcset="%s"%s />',
            esc_attr($media),
            esc_attr($srcset),
            $sizes_attribute
        );
    }
}

if (!function_exists('amnesty_image_block_attachment_src')) {
    /**
     * Read attachment source data for the responsive Image block.
     *
     * @param int          $attachment_id Attachment ID.
     * @param string|array $size          Image size.
     *
     * @return array{url:string,width:int,height:int}
     */
    function amnesty_image_block_attachment_src(int $attachment_id, string|array $size): array
    {
        if (! function_exists('wp_get_attachment_image_src')) {
            return [
                'url' => '',
                'width' => 0,
                'height' => 0,
            ];
        }

        $src = wp_get_attachment_image_src($attachment_id, $size);

        if (! is_array($src) || empty($src[0])) {
            return [
                'url' => '',
                'width' => 0,
                'height' => 0,
            ];
        }

        return [
            'url' => (string) $src[0],
            'width' => (int) ($src[1] ?? 0),
            'height' => (int) ($src[2] ?? 0),
        ];
    }
}

if (!function_exists('amnesty_image_block_responsive_picture_style')) {
    /**
     * Build the responsive aspect-ratio style for the picture wrapper.
     *
     * @param int          $desktop_image_id Desktop attachment ID.
     * @param int          $mobile_image_id  Mobile attachment ID.
     * @param string|array $size             Image size.
     *
     * @return string
     */
    function amnesty_image_block_responsive_picture_style(int $desktop_image_id, int $mobile_image_id, string|array $size): string
    {
        $mobile_src = amnesty_image_block_attachment_src($mobile_image_id, $size);
        $desktop_src = amnesty_image_block_attachment_src($desktop_image_id, $size);
        $styles = [];

        if ($mobile_src['width'] > 0 && $mobile_src['height'] > 0) {
            $styles[] = sprintf('--image-mobile-aspect-ratio: %d / %d;', $mobile_src['width'], $mobile_src['height']);
        }

        if ($desktop_src['width'] > 0 && $desktop_src['height'] > 0) {
            $styles[] = sprintf('--image-desktop-aspect-ratio: %d / %d;', $desktop_src['width'], $desktop_src['height']);
        }

        return $styles ? sprintf(' style="%s"', esc_attr(implode(' ', $styles))) : '';
    }
}

if (!function_exists('amnesty_image_block_img')) {
    /**
     * Render the fallback img for the responsive Image block picture.
     *
     * @param int                  $attachment_id Attachment ID.
     * @param string|array         $size          Image size.
     * @param array<string, mixed> $attr          Image attributes.
     *
     * @return string
     */
    function amnesty_image_block_img(int $attachment_id, string|array $size, array $attr = []): string
    {
        if (! function_exists('wp_get_attachment_image_src')) {
            return wp_get_attachment_image($attachment_id, $size, false, $attr);
        }

        $src = amnesty_image_block_attachment_src($attachment_id, $size);

        if ('' === $src['url']) {
            return wp_get_attachment_image($attachment_id, $size, false, $attr);
        }

        $default_attr = [
            'src' => $src['url'],
        ];

        if ($src['width'] > 0) {
            $default_attr['width'] = (string) $src['width'];
        }

        if ($src['height'] > 0) {
            $default_attr['height'] = (string) $src['height'];
        }

        $attr = array_merge($default_attr, $attr);

        $srcset = function_exists('wp_get_attachment_image_srcset')
            ? (string) wp_get_attachment_image_srcset($attachment_id, $size)
            : '';

        if ('' !== $srcset) {
            $attr['srcset'] = $srcset;
        }

        $sizes = function_exists('wp_get_attachment_image_sizes')
            ? (string) wp_get_attachment_image_sizes($attachment_id, $size)
            : '';

        if ('' !== $sizes) {
            $attr['sizes'] = $sizes;
        }

        $html_attributes = '';

        foreach ($attr as $name => $value) {
            if ('alt' !== $name && '' === (string) $value) {
                continue;
            }

            $html_attributes .= sprintf(
                ' %s="%s"',
                esc_attr((string) $name),
                'src' === $name ? esc_url((string) $value) : esc_attr((string) $value)
            );
        }

        return sprintf('<img%s />', $html_attributes);
    }
}

if (!function_exists('amnesty_image_block_responsive_picture')) {
    /**
     * Render one picture that serves the desktop image above the theme small breakpoint.
     *
     * @param int                  $desktop_image_id Desktop attachment ID.
     * @param int                  $mobile_image_id  Mobile attachment ID.
     * @param string|array         $size             Image size.
     * @param array<string, mixed> $attr             Image attributes.
     *
     * @return string
     */
    function amnesty_image_block_responsive_picture(int $desktop_image_id, int $mobile_image_id, string|array $size, array $attr = []): string
    {
        $media = '(min-width: 640px)';
        $desktop_img = amnesty_image_block_attachment_img($desktop_image_id, $size);
        $mobile_img = amnesty_image_block_attachment_img($mobile_image_id, $size, $attr);

        return sprintf(
            '<picture%s>%s%s</picture>',
            amnesty_image_block_responsive_picture_style($desktop_image_id, $mobile_image_id, $size),
            amnesty_image_block_source_from_img($desktop_img, $media),
            $mobile_img
        );
    }
}

if (!function_exists('render_image_block')) {
    /**
     * Render the Amnesty Image block
     *
     * @package Amnesty\Blocks
     *
     * @param array<string, mixed> $attributes Block attributes
     *
     * @return string
     */
    function render_image_block(array $attributes): string
    {
        if (empty($attributes['mediaId']) && empty($attributes['mediaMobileId'])) {
            return '<p>' . esc_html__('Aucune image sélectionnée', 'amnesty') . '</p>';
        }

        $image_id = (int) ($attributes['mediaId'] ?? 0);
        $mobile_image_id = (int) ($attributes['mediaMobileId'] ?? 0);
        $fallback_image_id = $image_id ?: $mobile_image_id;

        $image_alt = $image_id ? get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
        $mobile_image_alt = $mobile_image_id ? get_post_meta($mobile_image_id, '_wp_attachment_image_alt', true) : '';

        $image_post = $image_id ? get_post($image_id) : null;
        $mobile_image_post = $mobile_image_id ? get_post($mobile_image_id) : null;
        $caption = $image_post?->post_excerpt ?: $mobile_image_post?->post_excerpt;
        $description = $image_post?->post_content ?: $mobile_image_post?->post_content;

        $full_width = $attributes['fullWidth'] ?? false;
        $classes = (string) ($attributes['className'] ?? '');
        $show_metadata = ! str_contains(' ' . $classes . ' ', ' is-style-simple ');

        if ($full_width) {
            $classes = 'image-fullwidth ' . $classes;
        }

        ob_start();
        ?>

        <div class="image-block <?php echo esc_attr($classes) ?>">
            <div class="image-wrapper">
                <?php
                echo $image_id && $mobile_image_id
                    ? amnesty_image_block_responsive_picture($image_id, $mobile_image_id, 'full', [ 'alt' => $image_alt ?: $mobile_image_alt, 'loading' => 'lazy', 'decoding' => 'async' ])
                    : wp_get_attachment_image($fallback_image_id, 'full', false, [ 'alt' => $image_alt ?: $mobile_image_alt, 'loading' => 'lazy', 'decoding' => 'async' ]);
        ?>
                <?php if ($show_metadata && !empty($caption)) : ?>
                    <p class="image-caption"><?php echo esc_html($caption); ?></p>
                <?php endif; ?>
            </div>
			<?php if ($show_metadata && !empty($description)) : ?>
				<p class="image-description"><?php echo wp_kses_post($description); ?></p>
			<?php endif; ?>
        </div>

		<?php
        return ob_get_clean();
    }
}
