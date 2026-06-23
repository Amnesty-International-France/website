<?php

declare(strict_types=1);

/**
 * Builds a chronological archive of "actualités" posts grouped by year then month.
 *
 * Returns a nested structure ordered most-recent first:
 * [
 *   2026 => [
 *     6 => [ 'label' => 'Juin', 'items' => [ ['title' => ..., 'url' => ...], ... ] ],
 *     ...
 *   ],
 *   ...
 * ]
 *
 * Only titles, permalinks and dates are read, so the full archive stays light.
 * The result is cached statically to run the query only once per request.
 *
 * @return array<int, array<int, array{label: string, items: array<int, array{title: string, url: string}>}>>
 */
function aif_get_actualites_archive_grouped(): array
{
    static $grouped = null;

    if ($grouped !== null) {
        return $grouped;
    }

    $grouped = [];

    $query = new WP_Query([
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'category_name'          => '',
        'posts_per_page'         => -1,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    foreach ($query->posts as $post) {
        $year  = (int) get_the_date('Y', $post);
        $month = (int) get_the_date('n', $post);

        if (!isset($grouped[$year][$month])) {
            $grouped[$year][$month] = [
                'label' => ucfirst((string) wp_date('F', (int) get_post_timestamp($post))),
                'items' => [],
            ];
        }

        $grouped[$year][$month]['items'][] = [
            'title' => get_the_title($post),
            'url'   => (string) get_permalink($post),
        ];
    }

    wp_reset_postdata();

    return $grouped;
}
