<?php

/**
 * Riposte victory admin ordering.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Add ordering column.
 *
 * @param array<string,string> $columns Admin columns.
 *
 * @return array<string,string>
 */
function aif_riposte_ordering_columns(array $columns): array
{
	$new_columns = [];

	$new_columns['aif_riposte_order'] = '';

	foreach ($columns as $key => $label) {
		$new_columns[ $key ] = $label;
	}

	return $new_columns;
}
add_filter('manage_riposte_victory_posts_columns', 'aif_riposte_ordering_columns');

/**
 * Render ordering column.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 *
 * @return void
 */
function aif_riposte_ordering_column_content(string $column, int $post_id): void
{
	if ('aif_riposte_order' !== $column) {
		return;
	}

	printf(
		'<span class="aif-riposte-sort-handle" aria-hidden="true" data-post-id="%d">↕</span>',
		absint($post_id)
	);
}
add_action('manage_riposte_victory_posts_custom_column', 'aif_riposte_ordering_column_content', 10, 2);


/**
 * Save ordering.
 *
 * @return void
 */
function aif_riposte_save_ordering(): void
{
	check_ajax_referer('aif_riposte_ordering', 'nonce');

	if (! current_user_can('edit_posts')) {
		wp_send_json_error(
			[
				'message' => __('Vous n’avez pas les droits suffisants.', 'aif-riposte'),
			],
			403
		);
	}

	$ordered_ids = isset($_POST['order']) && is_array($_POST['order'])
		? array_map('absint', wp_unslash($_POST['order']))
		: [];

	$ordered_ids = array_filter($ordered_ids);

	if (empty($ordered_ids)) {
		wp_send_json_error(
			[
				'message' => __('Aucun ordre reçu.', 'aif-riposte'),
			],
			400
		);
	}

	foreach ($ordered_ids as $index => $post_id) {
		if ('riposte_victory' !== get_post_type($post_id)) {
			continue;
		}

		wp_update_post(
			[
				'ID'         => $post_id,
				'menu_order' => $index,
			]
		);
	}

	wp_send_json_success(
		[
			'message' => __('Ordre enregistré.', 'aif-riposte'),
		]
	);
}
add_action('wp_ajax_aif_riposte_save_ordering', 'aif_riposte_save_ordering');

/**
 * Force admin list order by menu_order.
 *
 * @param WP_Query $query Admin query.
 *
 * @return void
 */
function aif_riposte_admin_ordering_query(WP_Query $query): void
{
	if (! is_admin() || ! $query->is_main_query()) {
		return;
	}

	$screen = get_current_screen();

	if (! $screen || 'riposte_victory' !== $screen->post_type) {
		return;
	}

	if (! empty($_GET['orderby']) ) {
		return;
	}

	$query->set(
		'orderby',
		[
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		]
	);
}
add_action('pre_get_posts', 'aif_riposte_admin_ordering_query');