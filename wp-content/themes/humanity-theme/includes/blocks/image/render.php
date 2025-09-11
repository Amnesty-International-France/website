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
        if (empty($attributes['mediaId'])) {
            return '<p>' . esc_html__('Aucune image sélectionnée', 'amnesty') . '</p>';
        }

        $image_id = (int) $attributes['mediaId'];
        $image_url = wp_get_attachment_image_url($image_id, 'large');
        $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

        $image_post = get_post($image_id);
        $caption = $image_post->post_excerpt;
        $description = $image_post->post_content;

        $classes = $attributes['className'];

        ob_start();
        ?>

        <div class="image-block <?php echo esc_attr($classes) ?>">
            <div class="image-wrapper">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" />
                <?php if (!empty($caption)) : ?>
                    <p class="image-caption"><?php echo esc_html($caption); ?></p>
                <?php endif; ?>
            </div>
			<?php if (!empty($description)) : ?>
				<p class="image-description"><?php echo wp_kses_post($description); ?></p>
			<?php endif; ?>
        </div>

		<?php
        return ob_get_clean();
    }
}
