<?php

declare(strict_types=1);

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
            <?php if ($image_id && $mobile_image_id) : ?>
                <div class="image-wrapper image-device image-device-desktop">
                    <?php echo amnesty_get_attachment_picture($image_id, 'full', [ 'alt' => $image_alt ?: $mobile_image_alt, 'loading' => 'lazy', 'decoding' => 'async' ]); ?>
                    <?php if ($show_metadata && !empty($caption)) : ?>
                        <p class="image-caption"><?php echo esc_html($caption); ?></p>
                    <?php endif; ?>
                </div>
                <div class="image-wrapper image-device image-device-mobile">
                    <?php echo amnesty_get_attachment_picture($mobile_image_id, 'full', [ 'alt' => $mobile_image_alt ?: $image_alt, 'loading' => 'lazy', 'decoding' => 'async' ]); ?>
                    <?php if ($show_metadata && !empty($caption)) : ?>
                        <p class="image-caption"><?php echo esc_html($caption); ?></p>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="image-wrapper">
                    <?php echo amnesty_get_attachment_picture($fallback_image_id, 'full', [ 'alt' => $image_alt ?: $mobile_image_alt, 'loading' => 'lazy', 'decoding' => 'async' ]); ?>
                    <?php if ($show_metadata && !empty($caption)) : ?>
                        <p class="image-caption"><?php echo esc_html($caption); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
			<?php if ($show_metadata && !empty($description)) : ?>
				<p class="image-description"><?php echo wp_kses_post($description); ?></p>
			<?php endif; ?>
        </div>

		<?php
        return ob_get_clean();
    }
}
