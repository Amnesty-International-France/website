<?php

/**
 * Riposte victory load more.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}


/**
 * AJAX load more handler.
 *
 * @return void
 */
function aif_riposte_load_more(): void
{
    check_ajax_referer('aif_riposte_load_more', 'nonce');

    $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

    $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : AIF_RIPOSTE_POSTS_PER_PAGE;

    if ($per_page < 1 || $per_page > 10) {
        $per_page = AIF_RIPOSTE_POSTS_PER_PAGE;
    }

    $query_args = [
        'post_type'           => 'riposte_victory',
        'post_status'         => 'publish',
        'posts_per_page'      => $per_page,
        'offset'              => $offset,
        'ignore_sticky_posts' => true,
        'orderby'             => [
            'menu_order' => 'ASC',
            'date'       => 'DESC',
        ],
    ];

    $filters = [];

    foreach (aif_riposte_get_filter_taxonomies() as $taxonomy) {
        $key = sprintf('q%s', $taxonomy);

        $filters[ $taxonomy ] = isset($_POST[ $key ])
            ? wp_unslash($_POST[ $key ])
            : null;
    }

    $tax_query = aif_riposte_build_tax_query($filters);

    if (! empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    ob_start();
    $card_index = $offset;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            aif_riposte_render_card(null, $card_index);
            $card_index++;
        }
    }

    wp_reset_postdata();

    $returned_posts = $query->post_count;

    wp_send_json_success(
        [
            'html'     => ob_get_clean(),
            'hasMore' => ($offset + $returned_posts) < (int) $query->found_posts,
        ]
    );
}
add_action('wp_ajax_aif_riposte_load_more', 'aif_riposte_load_more');
add_action('wp_ajax_nopriv_aif_riposte_load_more', 'aif_riposte_load_more');
