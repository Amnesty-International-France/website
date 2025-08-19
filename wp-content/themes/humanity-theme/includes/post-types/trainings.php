<?php
/**
 * Register Custom Post-Type: Training
 */
function amnesty_register_trainings_cpt(): void
{
	register_post_type(
		'training',
		array(
			'labels' => array(
				'name' => 'Formations',
				'singular_name' => 'Formation',
				'add_new' => 'Ajouter une Formation',
				'add_new_item' => 'Ajouter une nouvelle Formation',
				'edit_item' => 'Modifier la Formation',
				'new_item' => 'Nouvelle Formation',
				'view_item' => 'Voir la Formation',
				'search_items' => 'Rechercher une Formation',
				'not_found' => 'Aucune Formation trouvée',
				'not_found_in_trash' => 'Aucune Formation dans la corbeille',
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'formations'),
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

add_action('init', 'amnesty_register_trainings_cpt');

/**
 * Filters the main query on the 'training' CPT archive page based on ACF fields.
 */
function my_project_training_filters($query) {
    if (is_admin() || !$query->is_main_query() || !is_post_type_archive('training')) {
        return;
    }

    $meta_query = ['relation' => 'AND'];
    $conditions_added = false;

    if (isset($_GET['qcategories']) && !empty($_GET['qcategories'])) {
        $categories = explode(',', sanitize_text_field($_GET['qcategories']));
        $meta_query[] = ['key' => 'categories', 'value' => $categories, 'compare' => 'IN'];
        $conditions_added = true;
    }

    if (isset($_GET['qlieu']) && !empty($_GET['qlieu'])) {
        $locations = explode(',', sanitize_text_field($_GET['qlieu']));
        $meta_query[] = ['key' => 'lieu', 'value' => $locations, 'compare' => 'IN'];
        $conditions_added = true;
    }

    if (isset($_GET['qperiod']) && !empty($_GET['qperiod'])) {
        $periods = explode(',', sanitize_text_field($_GET['qperiod']));
        $date_or_query = ['relation' => 'OR'];
        foreach ($periods as $period) {
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                $date_or_query[] = ['key' => 'date', 'value' => $period, 'compare' => 'LIKE'];
            }
        }
        if (count($date_or_query) > 1) {
            $meta_query[] = $date_or_query;
            $conditions_added = true;
        }
    }

    if ($conditions_added) {
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'my_project_training_filters');

add_action('template_redirect', function () {

	if (is_singular('training')) {
		global $post;

		$isPrivate = get_field('members_only', $post->ID);

		if ($isPrivate) {
			if (!is_user_logged_in()) {
				wp_redirect(home_url('/mon-espace/'));
				exit;
			}

			if (!current_user_can('administrator' || !current_user_can('subscriber'))) {
				wp_die('⚠️ Vous n’avez pas les permissions nécessaires pour accéder à cette page.');
			}
		}
	}
});
