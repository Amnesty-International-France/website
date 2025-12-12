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
            function get_dynamic_category_or_post_type_link(string $slug): string
            {
                if ($slug === 'landmark') {
                    return esc_url(get_post_type_archive_link('landmark') ?: home_url('/reperes'));
                } elseif ($slug === 'training') {
                    $obj = get_queried_object();
                    $my_space = get_page_by_path('mon-espace');
                    if ($my_space) {
                        $ancestors = get_post_ancestors($obj);
                        if (in_array($my_space->ID, $ancestors)) {
                            return get_permalink(get_page_by_path('militants-se-former'));
                        }
                    }
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
            function see_all_label(string $slug): string
            {
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

        if (!function_exists('find_redirect_link_for_trainings')) {
            function find_redirect_link_for_trainings(string $post_type, array $post_ids): string
            {
                $base_url = get_dynamic_category_or_post_type_link($post_type);
                if (empty($post_ids)) {
                    return $base_url;
                }

                $first_location = get_field('lieu', get_post($post_ids[0]));
                for ($i = 1; $i < count($post_ids); $i++) {
                    if (get_field('lieu', get_post($post_ids[$i])) !== $first_location) {
                        return $base_url;
                    }
                }

                return add_query_arg('qlieu', $first_location, $base_url);
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

        $redirect_link = $post_type === 'training' ? find_redirect_link_for_trainings($post_type, $post_ids) : get_dynamic_category_or_post_type_link($post_type);
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
                    <a class="cat-link" href="<?= $redirect_link ?>">
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
                            <?php while ($slider_query->have_posts()): $slider_query->the_post();
                                $current_post_id = get_the_ID();

                                $date_to_use = get_the_date();
                                $session_start = null;
                                $session_end = null;

                                if ($post_type === 'training') {
                                    $sessions_meta = get_post_meta($current_post_id, '', false);
                                    $raw_start_date_key = null;
                                    $raw_start = null;

                                    foreach ($sessions_meta as $key => $values) {
                                        if (strpos($key, 'session') !== false && strpos($key, 'date') !== false && strpos($key, 'debut') !== false) {
                                            if (!empty($values[0]) && $values[0] !== 'field_...') {
                                                $raw_start_date_key = $key;
                                                $raw_start = $values[0];
                                                break;
                                            }
                                        }
                                    }

                                    if (!empty($raw_start)) {
                                        $date_de_debut = DateTimeImmutable::createFromFormat('Ymd', (string) $raw_start);
                                        if ($date_de_debut) {
                                            $session_start = $date_de_debut->format('d/m/Y');
                                        } else {
                                            $session_start = (string) $raw_start;
                                        }

                                        if ($raw_start_date_key) {
                                            $raw_end_date_key = str_replace('debut', 'fin', $raw_start_date_key);
                                            $raw_end = get_post_meta($current_post_id, $raw_end_date_key, true);

                                            if (!empty($raw_end)) {
                                                $date_de_fin = DateTimeImmutable::createFromFormat('Ymd', (string) $raw_end);
                                                if ($date_de_fin) {
                                                    $session_end = $date_de_fin->format('d/m/Y');
                                                } else {
                                                    $session_end = (string) $raw_end;
                                                }
                                            }
                                        }

                                        if ($session_start) {
                                            if ($session_end) {
                                                $date_to_use = sprintf('Du %s au %s', $session_start, $session_end);
                                            } else {
                                                $date_to_use = sprintf('Le %s', $session_start);
                                            }
                                        }
                                    }
                                }
                                ?>
                                <div class="swiper-slide">
                                    <?php
                                        $args = [
                                            'direction'     => 'portrait',
                                            'post_id'       => $current_post_id,
                                            'title'         => get_the_title(),
                                            'permalink'     => get_permalink(),
                                            'date'          => $date_to_use,
                                            'thumbnail'     => get_the_post_thumbnail($current_post_id, 'medium', ['class' => 'article-image']),
                                            'main_category' => amnesty_get_a_post_term($current_post_id),
                                            'terms'         => wp_get_object_terms($current_post_id, get_object_taxonomies(get_post_type())),
                                            'session_start' => $session_start,
                                            'session_end'   => $session_end,
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
                                            <a href="<?= $redirect_link ?>" target="_blank" rel="noopener noreferrer" class="custom-button">
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
