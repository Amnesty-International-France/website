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

	$tax_query = aif_riposte_get_ajax_tax_query();

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

	wp_send_json_success(
		[
			'html'     => ob_get_clean(),
			'hasMore' => ($offset + $per_page) < (int) $query->found_posts,
		]
	);
}
add_action('wp_ajax_aif_riposte_load_more', 'aif_riposte_load_more');
add_action('wp_ajax_nopriv_aif_riposte_load_more', 'aif_riposte_load_more');

/**
 * Build AJAX tax query from request.
 *
 * @return array<int|string,mixed>
 */
function aif_riposte_get_ajax_tax_query(): array
{
	$tax_query = [];

	foreach ([ 'location', 'riposte_theme' ] as $taxonomy) {
		$key = sprintf('q%s', $taxonomy);

		if (empty($_POST[ $key ])) {
			continue;
		}

		$term_ids = is_array($_POST[ $key ])
			? array_map('absint', wp_unslash($_POST[ $key ]))
			: array_map('absint', explode(',', sanitize_text_field(wp_unslash($_POST[ $key ]))));

		$term_ids = array_values(array_unique(array_filter($term_ids)));

		if (empty($term_ids)) {
			continue;
		}

		$tax_query[] = [
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $term_ids,
		];
	}

	if (empty($tax_query)) {
		return [];
	}

	return [
		'relation' => 'AND',
		...$tax_query,
	];
}