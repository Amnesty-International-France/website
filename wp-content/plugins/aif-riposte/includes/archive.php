<?php

/**
 * Riposte victory archive behaviour.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Configure the Riposte Victory archive query.
 *
 * @param WP_Query $query Main query.
 *
 * @return void
 */
function aif_riposte_archive_query(WP_Query $query): void
{
	if (is_admin() || ! $query->is_main_query()) {
		return;
	}

	if (! is_post_type_archive('riposte_victory')) {
		return;
	}

	$query->set('posts_per_page', AIF_RIPOSTE_POSTS_PER_PAGE);
	$query->set('posts_per_archive_page', AIF_RIPOSTE_POSTS_PER_PAGE);
	$query->set(
		'orderby',
		[
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		]
	);
	$query->set('order', 'ASC');

	$tax_query = aif_riposte_get_archive_tax_query();

	if (! empty($tax_query)) {
		$query->set('tax_query', $tax_query);
	}
}
add_action('pre_get_posts', 'aif_riposte_archive_query');

/**
 * Build archive tax query from Humanity filter query vars.
 *
 * Expected query vars:
 * - qlocation
 * - qriposte_theme
 *
 * @return array<int|string,mixed>
 */
function aif_riposte_get_archive_tax_query(): array
{
	$tax_query = [];

	foreach ([ 'location', 'riposte_theme' ] as $taxonomy) {
		$term_ids = aif_riposte_get_filter_term_ids($taxonomy);

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

/**
 * Retrieve selected term IDs for a taxonomy.
 *
 * @param string $taxonomy Taxonomy name.
 *
 * @return array<int,int>
 */
function aif_riposte_get_filter_term_ids(string $taxonomy): array
{
	$query_var = sprintf('q%s', $taxonomy);
	$value     = get_query_var($query_var);

	if (empty($value) && isset($_GET[ $query_var ])) {
		$value = sanitize_text_field(wp_unslash($_GET[ $query_var ]));
	}

	if (empty($value)) {
		return [];
	}

	if (is_string($value)) {
		$value = explode(',', $value);
	}

	if (! is_array($value)) {
		return [];
	}

	$value = array_map('absint', $value);
	$value = array_filter($value);

	return array_values(array_unique($value));
}

/**
 * Register custom query vars used by Humanity archive filters.
 *
 * @param array<int,string> $vars Public query vars.
 *
 * @return array<int,string>
 */
function aif_riposte_register_query_vars(array $vars): array
{
	$vars[] = 'qlocation';
	$vars[] = 'qriposte_theme';

	return $vars;
}
add_filter('query_vars', 'aif_riposte_register_query_vars');