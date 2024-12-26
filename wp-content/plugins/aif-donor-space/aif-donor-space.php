<?php

/*
Plugin Name: Amnesty International France -  Donor Space
Description: A plugin to add Donor Space to Amnesty International France Website
Version: 1.0
Author: Fairness.coop for Amnesty International France
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
        'accueil' => 'AIF - Espace Donateur',
        'qui-etes-vous' => 'AIF - Qui êtes-vous ?'
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

function aif_donor_space_load_template($template)
{
    $page_slug = get_post_field('post_name', get_queried_object_id());

    $templates_dir = plugin_dir_path(__FILE__) . '/templates/';

    $templates_map = [
        'qui-etes-vous' =>  $templates_dir . 'check-email.php',
        'creer-votre-compte' => $templates_dir . 'create-account.php',
        'connectez-vous' => $templates_dir . 'login-user.php',
        'verifier-votre-email' => $templates_dir . '2FA-verification.php',
        'accueil' => $templates_dir . 'home.php',
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
        $plugin_url . 'assets/js/check-password.js',
        [],
        '1.0'
    );
}
add_action('wp_enqueue_scripts', 'aif_donor_space_enqueue_assets');
