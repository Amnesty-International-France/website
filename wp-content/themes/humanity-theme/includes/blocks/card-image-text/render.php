<?php

declare(strict_types=1);

if (!function_exists('render_card_image_text_block')) {
    /**
     * Render the "Card Image Text" block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string HTML output
     */
    function render_card_image_text_block(array $attributes): string
    {
        $custom = $attributes['custom'] ?? false;
        $postId = $attributes['postId'] ?? null;
        $direction = $attributes['direction'] ?? 'horizontal';
        $selected_post_category_slug = $attributes['selectedPostCategorySlug'] ?? '';

        if (! $custom && $postId) {
            $post = get_post($postId);
            $title = $post->post_title ?? '';
            $subtitle = get_the_date('d F, Y', $postId) ?? '';
            $permalink = get_permalink($postId);
            $thumbnail_id = get_post_thumbnail_id($postId);
            $text = $post->post_excerpt ?? '';

            $main_category = amnesty_get_a_post_term($postId);
            if (!($main_category instanceof WP_Term)) {
                $main_category = null;
            }

            if ($main_category) {
                $acf_singular = get_field('category_singular_name', $main_category);
                $default_label = $acf_singular ?: $main_category->name;

                $editorial_category = get_field('editorial_category', $postId);
                $category = $editorial_category && isset($editorial_category['label']) ? $editorial_category['label'] : $default_label;
                if ($editorial_category && isset($editorial_category['label'], $editorial_category['value'])) {
                    $category = get_editorial_category_singular_label($editorial_category['value']) ?: $editorial_category['label'];
                }
                $link = '';
            } else {
                $post_type = get_post_type($postId);
                if ('landmark' === $post_type) {
                    $repere_terms = wp_get_object_terms($postId, 'landmark_category');

                    if (!empty($repere_terms) && !is_wp_error($repere_terms)) {
                        $main_category = $repere_terms[0];
                        $category = $main_category->name;
                        $link = '';
                        $icon = match (strtolower($main_category->slug)) {
                            'decryptage' => 'decoding',
                            'droit-international' => 'employment-law',
                            'data' => 'data',
                            'desintox' => 'detox',
                            default => '',
                        };
                    }
                } elseif ('document' === $post_type) {
                    $document_terms = wp_get_object_terms($postId, 'document_category');
                    if (!empty($document_terms) && !is_wp_error($document_terms)) {
                        $main_category = $document_terms[0];
                        $category = $main_category->name;
                        $link = '';
                    }
                }

                if (empty($category)) {
                    $post_type_object = get_post_type_object($post_type);
                    $category = $post_type_object->labels->singular_name;
                    $link = get_post_type_archive_link($post_type);
                }
            }
        } else {
            $title = $attributes['title'] ?? '';
            $subtitle = $attributes['subtitle'] ?? '';
            $category = $attributes['category'] ?? '';
            $permalink = $attributes['permalink'] ?? '#';
            $thumbnail_id = $attributes['thumbnail'] ?? null;
            $text = $attributes['text'] ?? '';
        }

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
				<?= render_chip_category_block([
				    'label' => esc_html($category),
				    'link' => esc_url($link ?? '#'),
				    'size' => 'large',
				    'style' => 'card-image-text-category',
				    'isLandmark' => ($postId && ! $custom && 'landmark' === get_post_type($postId)),
				    'icon' => $icon ?? '',
				]) ?>
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
