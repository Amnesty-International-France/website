<?php

declare(strict_types=1);


/**
 * Register Custom Post Type: EDH
 */
function amnesty_register_edh_cpt()
{
	register_post_type(
		'edh',
		array(
			'labels' => array(
				'name' => 'EDH',
				'singular_name' => 'EDH',
				'add_new' => 'Ajouter un EDH',
				'add_new_item' => 'Ajouter un nouveau EDH',
				'edit_item' => 'Modifier un EDH',
				'new_item' => 'Nouveau EDH',
				'view_item' => 'Voir le EDH',
				'search_items' => 'Rechercher un EDH',
				'not_found' => 'Aucun EDH trouvé',
				'not_found_in_trash' => 'Aucun EDH dans la corbeille',
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'edh'),
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
			'menu_icon' => 'dashicons-admin-page',
			'show_in_rest' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 20,
		)
	);
}

add_action('init', 'amnesty_register_edh_cpt');

/**
 * Filters the main query on the 'edh' CPT archive page based on ACF fields.
 */
function my_project_edh_filters($query) {
	if (is_admin() || !$query->is_main_query() || !is_post_type_archive('edh')) {
		return;
	}

	$meta_query = ['relation' => 'AND'];
	$conditions_added = false;

	if (isset($_GET['qcontent_type']) && !empty($_GET['qcontent_type'])) {
		$content_type = explode(',', sanitize_text_field($_GET['qcontent_type']));
		$meta_query[] = ['key' => 'content_type', 'value' => $content_type, 'compare' => 'IN'];
		$conditions_added = true;
	}

	if (isset($_GET['qtheme']) && !empty($_GET['qtheme'])) {
		$requirements = explode(',', sanitize_text_field($_GET['qtheme']));
		$meta_query[] = ['key' => 'theme', 'value' => $requirements, 'compare' => 'IN'];
		$conditions_added = true;
	}

	if (isset($_GET['qrequirements']) && !empty($_GET['qrequirements'])) {
		$requirements = explode(',', sanitize_text_field($_GET['qrequirements']));
		$meta_query[] = ['key' => 'requirements', 'value' => $requirements, 'compare' => 'IN'];
		$conditions_added = true;
	}

	if (isset($_GET['qactivity_duration']) && !empty($_GET['qactivity_duration'])) {
		$activity_duration = explode(',', sanitize_text_field($_GET['qactivity_duration']));
		$meta_query[] = ['key' => 'activity_duration', 'value' => $activity_duration, 'compare' => 'IN'];
		$conditions_added = true;
	}

	if ($conditions_added) {
		$query->set('meta_query', $meta_query);
	}
}
add_action('pre_get_posts', 'my_project_edh_filters');
