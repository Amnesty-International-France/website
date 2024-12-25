<?php

/*
Plugin Name: AIF Donor Space 2
Description: A plugin to add a custom page with a custom template to WordPress.
Version: 1.0
Author: Your Name
*/


// Empêcher l'accès direct
if (! defined('ABSPATH')) {
    exit;
}


/*
/ Includes
*/

include_once plugin_dir_path(__FILE__) . 'includes/test.php';

/*
/ Configure Child Theme
*/
require_once plugin_dir_path(__FILE__) . '/includes/child-theme/configure.php';


/*
/ Sales Force
*/
require_once plugin_dir_path(__FILE__) . '/includes/sales-force/authentification.php';
require_once plugin_dir_path(__FILE__) . '/includes/sales-force/user-data.php';


/*
/  2FA
*/
require_once plugin_dir_path(__FILE__) . '/includes/2FA/index.php';


/*
/ Configure Plugin
*/
function aif_donor_space_create_pages()
{

    $pages = [
        'verifier-votre-email' => 'AIF - Vérifier votre email',
    ];

    foreach ($pages as $slug => $title) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        }
    }
}
register_activation_hook(__FILE__, 'aif_donor_space_create_pages');

// Fonction pour charger le template personnalisé
function aif_donor_space_load_template($template)
{
    $page_slug = get_post_field('post_name', get_queried_object_id());

    $templates_dir = plugin_dir_path(__FILE__) . '/templates/';

    $templates_map = [
        'donor-page-1' => $templates_dir . 'template-page-1.php',
    ];

    if (array_key_exists($page_slug, $templates_map) && file_exists($templates_map[ $page_slug ])) {
        return $templates_map[ $page_slug ];
    }

    return $template;
}
add_filter('template_include', 'aif_donor_space_load_template');
