<?php

/*
Plugin Name: AIF Donor Space
Description: A plugin to add a custom page with a custom template to WordPress.
Version: 1.0
Author: Fairness coop for Amnesty International France
*/

if (! defined('ABSPATH')) {
    exit;
}


/*
/ Includes
*/

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
        'creer-votre-compte' => 'AIF - Créer votre compte',
        'connectez-vous' => 'AIF - Connectez-vous',

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
        'verifier-votre-email' => $templates_dir . 'check-email.php',
        'creer-votre-compte' => $templates_dir . 'create-account.php',
        'connectez-vous' => $templates_dir . 'login-user.php',
    ];

    if (array_key_exists($page_slug, $templates_map) && file_exists($templates_map[ $page_slug ])) {
        return $templates_map[ $page_slug ];
    }

    return $template;
}
add_filter('template_include', 'aif_donor_space_load_template');


function aif_donor_space_enqueue_assets()
{
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style(
        'aif-donor-space-style',
        $plugin_url . 'assets/css/style.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'aif-donor-space-script',
        $plugin_url . 'assets/js/main.js',
        array(),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'aif_donor_space_enqueue_assets');
