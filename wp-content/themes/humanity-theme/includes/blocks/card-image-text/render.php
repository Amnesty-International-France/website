<?php

declare(strict_types=1);

if (!function_exists('render_card_image_text_block')) {
    /**
     * Render the "Card Image Text" block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_card_image_text_block(array $attributes): string {
        $custom = $attributes['custom'] ?? false;
        $direction = $attributes['direction'] ?? 'horizontal';
        $selected_post_category_slug = $attributes['selectedPostCategorySlug'] ?? '';

        $title = $attributes['title'] ?? '';
        $subtitle = $attributes['subtitle'] ?? '';
        $category = $attributes['category'] ?? '';
        $permalink = $attributes['permalink'] ?? '#';
        $thumbnail_id = $attributes['thumbnail'] ?? null;
        $text = $attributes['text'] ?? '';
        
        $thumbnail_url = '';
        if ($thumbnail_id) {
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large');
        }

        $link_extra_attrs = '';
        if ($custom || $selected_post_category_slug === 'document') {
            $link_extra_attrs = ' target="_blank" rel="noopener noreferrer"';
        }

        ob_start();

        $wrapper_classes = ['card-image-text-block', $direction];
        ?>
        <div <?php echo get_block_wrapper_attributes(['class' => implode(' ', $wrapper_classes)]); ?>>
            <?php if (!empty($category)) : ?>
                <p class="card-image-text-category"><?php echo esc_html($category); ?></p>
            <?php endif; ?>
            <div class="card-content-wrapper">
                <a href="<?php echo esc_url($permalink); ?>" class="card-image-text-block-link"<?php echo $link_extra_attrs; ?>>
                    <div class="card-image-text-thumbnail-wrapper">
                        <?php if (!empty($thumbnail_url)) : ?>
                            <img class="card-image-text-thumbnail" src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" />
                        <?php endif; ?>
                    </div>
                    <div class="card-image-text-content-container">
                        <div class="card-image-text-content">
                            <?php if (!empty($subtitle)) : ?>
                                <p class="card-image-text-content-subtitle"><?php echo esc_html($subtitle); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($title)) : ?>
                                <p class="card-image-text-content-title"><?php echo esc_html($title); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($text)) : ?>
                                <div class="card-image-text-content-text"><?php echo wp_kses_post($text); ?></div>
                            <?php endif; ?>
                            <div class="card-image-text-content-see-more">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                >
                                    <path
                                        fillRule="evenodd"
                                        clipRule="evenodd"
                                        d="M10.7826 7.33336L7.20663 3.75736L8.1493 2.8147L13.3346 8.00003L8.1493 13.1854L7.20663 12.2427L10.7826 8.6667H2.66797V7.33336H10.7826Z"
                                        fill="black"
                                    />
                                </svg>
                                <p class="card-image-text-content-see-more-label">Voir la suite</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
