<?php
declare(strict_types=1);

if (!function_exists('render_slider_block')) {
    /**
     * Render the Slider block
     *
     * @param array<string, mixed> $attributes Block attributes.
     * @return string HTML output.
     */
    function render_slider_block(array $attributes): string
    {
        $post_type = $attributes['postType'] ?? '';
        $selected_posts = $attributes['selectedPosts'] ?? [];
    
        if (empty($post_type) || empty($selected_posts) || count($selected_posts) < 4) {
            return '';
        }

        if (!function_exists('get_dynamic_category_or_post_type_link')) {
            function get_dynamic_category_or_post_type_link(string $slug): string {
                if ($slug === 'landmark') {
                    return esc_url(get_post_type_archive_link('landmark') ?: home_url('/reperes'));
                } elseif ($slug === 'training') {
                    return esc_url(get_post_type_archive_link('training') ?: home_url('/formations')); 
                } elseif ($slug === 'edh') {
                    return esc_url(get_post_type_archive_link('edh') ?: home_url('/edh'));
                } elseif ($slug === 'petition') {
                    return esc_url(get_post_type_archive_link('petition') ?: home_url('/petitions'));          
                } else {
                    $category = get_category_by_slug($slug);
                    if ($category) {
                        return esc_url(get_category_link($category->term_id));
                    }
                    return esc_url(home_url("/{$slug}"));
                }
            }
        }

        if (!function_exists('see_all_label')) {
            function see_all_label(string $slug): string {
                switch ($slug) {
                    case 'actualites': return 'Voir toutes les actualités';
                    case 'campagnes': return 'Voir toutes les campagnes';
                    case 'chroniques': return 'Voir tous les articles la chronique';
                    case 'dossiers': return 'Voir tous les dossiers';
                    case 'landmark': return 'Voir tous les repères';
                    case 'training': return 'Voir toutes les formations';
                    case 'edh': return 'Voir toutes les ressources pédagogiques';
                    case 'petition': return 'Voir toutes les pétitions';
                    case 'document': return 'Voir touts les documents';
                    default: return 'Voir tous les articles';
                }
            }
        }

        $post_ids = wp_list_pluck($selected_posts, 'id');
        $query_args = [
            'post_type'      => 'any',
            'post__in'       => $post_ids,
            'posts_per_page' => count($post_ids),
            'orderby'        => 'post__in',
        ];
        $slider_query = new WP_Query($query_args);

        if (!$slider_query->have_posts()) {
            return '';
        }
    
        ob_start();
        ?>
        <div class="slider-block">
            <?php if ($post_type !== 'document') : ?>
                <div class="category-link">
                    <div class="icon-container">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </div>
                    <a class="cat-link" href="<?= get_dynamic_category_or_post_type_link($post_type) ?>">
                        <?= esc_html(see_all_label($post_type)) ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="slider-block-wrapper">
                <div class="slider-container">
                    <div class="slider-nav prev">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M11 6L21 16L11 26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <?php while ($slider_query->have_posts()): $slider_query->the_post(); ?>
                                <div class="swiper-slide">
                                    <?php
                                    $args = [
                                        'direction'     => 'portrait',
                                        'post_id'       => get_the_ID(),
                                        'title'         => get_the_title(),
                                        'permalink'     => get_permalink(),
                                        'date'          => get_the_date(),
                                        'thumbnail'     => get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'article-image']),
                                        'main_category' => amnesty_get_a_post_term(get_the_ID()),
                                        'terms'         => wp_get_object_terms(get_the_ID(), get_object_taxonomies(get_post_type())),
                                    ];
        
                                    $template_path = locate_template('partials/article-card.php');
                                    if ($template_path) {
                                        extract($args);
                                        include $template_path;
                                    }
                                    ?>
                                </div>
                            <?php endwhile; ?>

                            <?php if ($post_type !== 'document') : ?>
                                <div class="swiper-slide">
                                    <div class="article-card see-all-card">
                                        <div class='custom-button-block center'>
                                            <a href="<?= get_dynamic_category_or_post_type_link($post_type) ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
                                                <div class='content bg-yellow small'>
                                                    <div class="icon-container">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            strokeWidth="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                        </svg>
                                                    </div>
                                                    <div class="button-label">Tout voir</div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="slider-nav next">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M11 6L21 16L11 26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
}
