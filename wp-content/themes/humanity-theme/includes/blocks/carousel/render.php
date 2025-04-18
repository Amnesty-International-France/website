<?php

declare(strict_types=1);

if (!function_exists('render_carousel_block')) {
    /**
     * Render the carousel block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_carousel_block(array $attributes): string {
        $images = $attributes['images'] ?? [];

        if (empty($images)) {
            return '';
        }

        ob_start();
        ?>
        <div class="carousel-block">
            <div class="swiper">
                <div class="swiper-wrapper">
                <?php foreach ($images as $image): ?>
                    <div class="swiper-slide">
                    <img
                        src="<?php echo esc_url($image['url'] ?? ''); ?>"
                        alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
                        loading="lazy"
                    />
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
