<?php

declare(strict_types=1);

if (!function_exists('render_articles_homepage_block')) {
    /**
     * Render the Articles Homepage block
     *
     * @param array<string, mixed> $attributes
     *
     * @return string
     */
    function render_articles_homepage_block(array $attributes): string
    {
        $items = $attributes['items'] ?? [];
        $class_name = esc_attr($attributes['className'] ?? '');

        if (empty($items) || !is_array($items)) {
            return '';
        }

        /**
         * Helper function to get the correct link based on slug.
         *
         * @param string $slug The category or post type slug.
         * @return string The URL.
         */
        if (!function_exists('get_dynamic_category_or_post_type_link')) {
            function get_dynamic_category_or_post_type_link(string $slug): string
            {
                if ($slug === 'landmark') {
                    return esc_url(get_post_type_archive_link('landmark') ?: home_url('/reperes'));
                } else {
                    $category = get_category_by_slug($slug);
                    if ($category) {
                        return esc_url(get_category_link($category->term_id));
                    }
                    return esc_url(home_url("/{$slug}"));
                }
            }
        }

        /**
         * Helper function to format the date for a specific post.
         *
         * @param int|WP_Post $post The post ID or post object.
         * @return string Formatted date.
         */
        if (!function_exists('format_post_date')) {
            function format_post_date($post): string
            {
                return get_the_date('j F Y', $post);
            }
        }

        /**
         * Helper function to get custom terms for a post.
         *
         * @param int $post_id The ID of the post.
         * @return array<array<string, string>> Array of custom terms (id, name, slug, taxonomy).
         */
        if (!function_exists('get_post_custom_terms')) {
            function get_post_custom_terms(int $post_id): array
            {
                $custom_terms_data = [];
                $post_type = get_post_type($post_id);
                $taxonomies = get_object_taxonomies($post_type, 'objects');

                foreach ($taxonomies as $taxonomy_slug => $taxonomy_obj) {
                    if ($taxonomy_slug === 'category' || $taxonomy_slug === 'post_tag') {
                        continue;
                    }

                    $terms = get_the_terms($post_id, $taxonomy_slug);

                    if (!is_wp_error($terms) && !empty($terms)) {
                        foreach ($terms as $term) {
                            $custom_terms_data[] = [
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'slug' => $term->slug,
                                'taxonomy' => $taxonomy_slug,
                            ];
                        }
                    }
                }
                return $custom_terms_data;
            }
        }


        $posts = [];
        foreach ($items as $item) {
            $selected_id = isset($item['selectedPostId']) ? (int)$item['selectedPostId'] : 0;
            $category_slug_from_attributes = $item['category'] ?? '';
            $subtitle = esc_html($item['subtitle'] ?? '');

            $post = null;
            if ($selected_id) {
                $post = get_post($selected_id);
            } elseif ($category_slug_from_attributes) {
                $args = [
                    'posts_per_page' => 1,
                    'post_status' => 'publish',
                ];

                $category_obj = get_category_by_slug($category_slug_from_attributes);
                if ($category_obj) {
                    $args['category'] = $category_obj->term_id;
                } elseif (post_type_exists($category_slug_from_attributes)) {
                    $args['post_type'] = $category_slug_from_attributes;
                } else {
                    continue;
                }
                $fetched_posts = get_posts($args);
                $post = $fetched_posts[0] ?? null;
            }

            if (!$post) {
                continue;
            }

            $image_url = get_the_post_thumbnail_url($post, 'medium');

            $current_post_type = get_post_type($post);
            $entity_slug = '';
            $entity_label = '';
            $chip_style = 'outline-black';

            if ($current_post_type === 'post') {
                $main_category = amnesty_get_a_post_term($post->ID);
                if (is_a($main_category, 'WP_Term')) {
                    $entity_slug = $main_category->slug ?? '';
                    $entity_label = get_field('category_singular_name', $main_category) ?: $main_category->name;
                }
            } elseif ($current_post_type === 'landmark') {
                $entity_slug = 'landmark';
                $entity_label = 'Repère';
            }

            if (empty($entity_slug) && $category_slug_from_attributes) {
                $entity_slug = $category_slug_from_attributes;
                if ($entity_slug === 'landmark') {
                    $entity_label = 'Repère';
                } else {
                    $fallback_cat = get_category_by_slug($entity_slug);
                    $entity_label = $fallback_cat ? (get_field('category_singular_name', $fallback_cat) ?: $fallback_cat->name) : ucwords(str_replace('-', ' ', $entity_slug));
                }
            }

            $chip_style = match ($entity_slug) {
                'actualites', 'chroniques', 'landmark' => 'bg-yellow',
                'dossiers', 'campagnes' => 'bg-black',
                default => 'outline-black',
            };

            $posts[] = [
                'post'          => $post,
                'subtitle'      => $subtitle,
                'image'         => $image_url,
                'entity_slug'   => $entity_slug,
                'chip_style'    => $chip_style,
                'entity_label'  => $entity_label,
                'post_date'     => format_post_date($post),
                'custom_terms'  => get_post_custom_terms($post->ID),
            ];
        }

        if (empty($posts)) {
            return '';
        }

        if (!function_exists('see_all_label')) {
            /**
             * Get the "See all" label based on category or post type slug.
             *
             * @param string $slug The category or post type slug.
             * @return string
             */
            function see_all_label(string $slug): string
            {
                switch ($slug) {
                    case 'actualites':
                        return 'Voir toutes les actualités';
                    case 'campagnes':
                        return 'Voir toutes les campagnes';
                    case 'chroniques':
                        return 'Voir tous les articles la chronique';
                    case 'dossiers':
                        return 'Voir tous les dossiers';
                    case 'landmark':
                        return 'Voir tous les repères';
                    default:
                        return 'Voir tous les articles';
                }
            }
        }

        ob_start();
        ?>
        <section class="articles-homepage<?php echo $class_name; ?>">
            <div class="articles-homepage-wrapper">

                <h2 class="title"><?php esc_html_e('À la une', 'amnesty'); ?></h2>

                <div class="articles-homepage-container">
                    <?php
                    if (isset($posts[0])):
                        $post = $posts[0]['post'];
                        $subtitle = $posts[0]['subtitle'];
                        $title = get_the_title($post);
                        $url = get_permalink($post);
                        $image = $posts[0]['image'];
                        $entity_slug = $posts[0]['entity_slug'];
                        $chip_style = $posts[0]['chip_style'];
                        $entity_label = $posts[0]['entity_label'];
                        $post_date = $posts[0]['post_date'];
                        $custom_terms = $posts[0]['custom_terms'];
                        ?>

                    <div class="article-main-desktop">
                        <?php if (!empty($entity_slug)): ?>
                            <?= render_chip_category_block([
                                'label' => esc_html($entity_label),
                                'size'  => 'small',
                                'link'  => get_dynamic_category_or_post_type_link($entity_slug),
                                'style' => esc_attr($chip_style),
                            ]) ?>
                        <?php endif; ?>
                        <?php if ($image): ?>
                            <div class="article-image-container">
                                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                                    <img class="article-image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
                                    <div class="article-content">
                                        <div class="article-title-wrapper">
                                            <h3 class="article-title"><?php echo esc_html($title); ?></h3>
                                        </div>
                                        <?php if ($subtitle): ?>
                                            <div class="article-subtitle-wrapper">
                                                <p class="article-subtitle"><?php echo $subtitle; ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <div class="article-button-wrapper">
                                            <a href="<?php echo esc_url($url); ?>" class="article-button" target="_blank" rel="noopener noreferrer">
                                                <?php esc_html_e('Lire la suite', 'amnesty'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php if (!empty($entity_slug)) : ?>
                                <div class="category-link">
                                    <div class="icon-container">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                        </svg>
                                    </div>
                                    <a class="link" href="<?= get_dynamic_category_or_post_type_link($entity_slug) ?>">
                                        <?= esc_html(see_all_label($entity_slug)) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="article-main-mobile">
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
                            <div class="wrapper">
                                <?php if (!empty($entity_slug)): ?>
                                    <?= render_chip_category_block([
                                        'label' => esc_html($entity_label),
                                        'size'  => 'small',
                                        'style' => esc_attr($chip_style),
                                    ]) ?>
                                <?php endif; ?>
                                <div class="article-main-mobile-image-wrapper">
                                    <?php if ($image): ?>
                                        <img class="article-main-mobile-image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
                                    <?php endif; ?>
                                </div>
                                <div class="article-main-mobile-content">
                                    <div class="article-main-mobile-header">
                                        <?php if (!empty($post_date)): ?>
                                            <p class="article-date"><?php echo esc_html($post_date); ?></p>
                                        <?php endif; ?>
                                        <h3 class="article-title"><?php echo esc_html($title); ?></h3>
                                    </div>
                                    <?php if (!empty($custom_terms)): ?>
                                        <div class="article-main-mobile-footer">
                                            <?php foreach ($custom_terms as $term): ?>
                                                <span class="term <?php echo esc_attr($term['taxonomy']); ?>">
                                                    <?php echo esc_html($term['name']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <?php if (!empty($entity_slug)) : ?>
                            <div class="category-link">
                                <div class="icon-container">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </div>
                                <a class="link" href="<?= get_dynamic_category_or_post_type_link($entity_slug) ?>">
                                    <?= esc_html(see_all_label($entity_slug)) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
                if (count($posts) > 1): ?>
                    <div class="articles-side-column">
                        <?php foreach (array_slice($posts, 1, 2) as $post_data):
                            $post = $post_data['post'];
                            $title = get_the_title($post);
                            $url = get_permalink($post);
                            $image = $post_data['image'];
                            $entity_slug = $post_data['entity_slug'];
                            $chip_style = $post_data['chip_style'];
                            $entity_label = $post_data['entity_label'];
                            $post_date = $post_data['post_date'];
                            $custom_terms = $post_data['custom_terms'];
                            ?>
                        <div class="article-side">
                            <a href="<?php echo esc_url(url: $url); ?>" target="_blank" rel="noopener noreferrer">
                                <div class="wrapper">
                                    <?php if (!empty($entity_slug)): ?>
                                        <?= render_chip_category_block([
                                            'label' => esc_html($entity_label),
                                            'size'  => 'small',
                                            'style' => esc_attr($chip_style),
                                        ]) ?>
                                    <?php endif; ?>
                                    <div class="article-side-image-wrapper">
                                        <?php if ($image): ?>
                                            <img class="article-side-image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="article-side-content">
                                        <div class="article-side-header">
                                            <?php if (!empty($post_date)): ?>
                                                <p class="article-date">
                                                    <?php echo esc_html($post_date); ?>
                                                </p>
                                            <?php endif; ?>
                                            <h3 class="article-title"><?php echo esc_html($title); ?></h3>
                                        </div>
                                        <?php if (!empty($custom_terms)): ?>
                                            <div class="article-side-footer">
                                                <?php foreach ($custom_terms as $term): ?>
                                                    <span class="term <?php echo esc_attr($term['taxonomy']); ?>">
                                                        <?php echo esc_html($term['name']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>

                            <?php if (!empty($entity_slug)) : ?>
                                <div class="category-link">
                                    <div class="icon-container">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                        </svg>
                                    </div>
                                    <a class="link" href="<?= get_dynamic_category_or_post_type_link($entity_slug) ?>">
                                        <?= esc_html(see_all_label($entity_slug)) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
