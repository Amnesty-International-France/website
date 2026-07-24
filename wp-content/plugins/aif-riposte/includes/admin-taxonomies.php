<?php

/**
 * Riposte victory admin taxonomies.
 *
 * @package AIF_Riposte
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

function aif_riposte_limit_single_taxonomy_terms(int $post_id): void
{
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	foreach ([ 'riposte_theme', 'riposte_tag', 'location' ] as $taxonomy) {
		$terms = wp_get_object_terms(
			$post_id,
			$taxonomy,
			[
				'fields' => 'ids',
			]
		);

		if (is_wp_error($terms) || count($terms) <= 1) {
			continue;
		}

		wp_set_object_terms(
			$post_id,
			[ (int) $terms[0] ],
			$taxonomy,
			false
		);
	}
}
add_action('save_post_riposte_victory', 'aif_riposte_limit_single_taxonomy_terms', 20);