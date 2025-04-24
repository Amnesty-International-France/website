<?php

declare(strict_types=1);

if (!function_exists('render_carousel_block')) {
    /**
     * Render the Carousel block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_carousel_block(array $attributes): string {
        $image_ids = $attributes['mediaIds'] ?? [];
    
        if (empty($image_ids)) {
            return '<p>' . esc_html__('Aucune image sélectionnée', 'amnesty') . '</p>';
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
                        ?>
                        <div class="swiper-slide">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" />
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
