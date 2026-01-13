<?php

declare(strict_types=1);

if (!function_exists('render_carousel_block')) {
    /**
     * Render the Carousel block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_carousel_block(array $attributes): string
    {
        $image_ids = $attributes['mediaIds'] ?? [];

        if (empty($image_ids) || count($image_ids) < 2) {
            if (is_admin()) {
                return '<p>' . esc_html__('Veuillez sélectionner au moins 2 images pour le carrousel.', 'amnesty') . '</p>';
            } else {
                return '';
            }
        }

        ob_start();
        ?>
        <div class="carousel-block">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($image_ids as $image_id): ?>
                        <?php
                        $image_url = wp_get_attachment_image_url($image_id, 'large');
                        $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        $caption = wp_get_attachment_caption($image_id);
                        $description = get_post_field('post_content', $image_id);
                        ?>
                        <div class="swiper-slide">
                            <div class="carousel-image">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
                                <?php if (!empty($caption)) : ?>
                                    <div class="carousel-caption">
                                        <span class="carousel-caption-text"><?php echo wp_kses_post($caption); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($description)) : ?>
                                <div class="carousel-description">
                                    <span class="carousel-description-text"><?php echo wp_kses_post($description); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-nav prev">&#10094;</div>
                <div class="carousel-nav next">&#10095;</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}
