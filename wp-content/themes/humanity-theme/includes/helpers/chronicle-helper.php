<?php

declare(strict_types=1);

/**
 * Finds key information about the structure of chronicle (promo page and archive page).
 * Uses a static cache to only run the complex search once per page.
 *
 * @return false|array|null
 */
function amnesty_get_chronicle_structure_info(): false|array|null
{
    static $chronicle_info = null;

    if ($chronicle_info !== null) {
        return $chronicle_info;
    }

    $chronicle_info = false;

    $chronicle_pages_query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'name'           => 'chronique',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
    ]);

    if ($chronicle_pages_query->have_posts()) {
        foreach ($chronicle_pages_query->posts as $chronicle_page) {
            $archives_page_query = new WP_Query([
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'name'           => 'archives',
                'post_parent'    => $chronicle_page->ID,
                'posts_per_page' => 1,
                'no_found_rows'  => true,
            ]);

            if ($archives_page_query->have_posts()) {
                $archives_page = $archives_page_query->post;
                $chronicle_info = [
                    'promo_page_id' => $chronicle_page->ID,
                    'archives_url'  => get_permalink($archives_page->ID),
                ];
                break;
            }
        }
    }
    wp_reset_postdata();

    return $chronicle_info;
}
