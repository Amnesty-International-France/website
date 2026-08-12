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
            return amnesty_get_attachment_picture($attachment_id, $size, $attr);
        }

        $src = wp_get_attachment_image_src($attachment_id, $size);

        if (! is_array($src) || empty($src[0])) {
            return amnesty_get_attachment_picture($attachment_id, $size, $attr);
        }

        $attr = array_merge(
            [
                'src' => (string) $src[0],
                'width' => (string) ($src[1] ?? ''),
                'height' => (string) ($src[2] ?? ''),
            ],
            $attr
        );

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
            if ('' === (string) $value) {
                continue;
            }

            $html_attributes .= sprintf(' %s="%s"', esc_attr((string) $name), esc_attr((string) $value));
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
        return sprintf(
            '<picture>%s%s</picture>',
            amnesty_image_block_source($desktop_image_id, $size, '(min-width: 640px)'),
            amnesty_image_block_img($mobile_image_id, $size, $attr)
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
                    ? amnesty_image_block_responsive_picture($image_id, $mobile_image_id, 'full', [ 'alt' => $mobile_image_alt ?: $image_alt, 'loading' => 'lazy', 'decoding' => 'async' ])
                    : amnesty_get_attachment_picture($fallback_image_id, 'full', [ 'alt' => $image_alt ?: $mobile_image_alt, 'loading' => 'lazy', 'decoding' => 'async' ]);
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
