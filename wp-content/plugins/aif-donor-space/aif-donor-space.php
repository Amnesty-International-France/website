<?php

ob_start();

/*
Plugin Name: Amnesty International France -  Donor Space
Description: A plugin to add Donor Space to Amnesty International France Website
Version: 1.0
Author: Fairness.coop for Amnesty International France
*/

if (! defined('ABSPATH')) {
    exit;
}

define("AIF_DONOR_SPACE_PATH", plugin_dir_path(__FILE__));
define('AIF_DONOR_SPACE_VERSION', '1.0.0');
define('AIF_DONOR_SPACE_URL', plugin_dir_url(__FILE__));


/*
/ Includes
*/

/*
/ Configuration
*/

require_once AIF_DONOR_SPACE_PATH. '/configuration.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/authorization.php';

/*
/ Sales Force
*/
require_once AIF_DONOR_SPACE_PATH. '/includes/sales-force/authentification.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/sales-force/user-data.php';


/*
/  Domain
*/
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/tax-receipt/rest-controllers.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/tax-receipt/index.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/bank/IBAN.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/bank/SEPA-mandate.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/2FA/index.php';
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/user-authentification.php';

require_once AIF_DONOR_SPACE_PATH. '/includes/utils.php';



/*
/ Configure Plugin
*/
function aif_donor_space_create_pages()
{

    $pages = [
        'espace-don' =>  [
            'title' =>  'Mon espace don',
            'children' => [
                'verifier-votre-email' =>  ['title' => 'Mon espace don - Vérifier votre email'],
                'creer-votre-compte' => ['title' => 'Mon espace don - Créer votre compte'],
                'connectez-vous' => ['title' => 'Mon espace don - Connectez-vous'],
                'mes-recus-fiscaux' => ['title' => 'Mon espace don - Reçus Fiscaux'],
                'modifier-mon-mot-de-passe' => ['title' => 'Mon espace don - Modifier mon mot de passe'],
                'mot-de-passe-oublie' => ['title' => 'Mon espace don - Mot de passe oublié'],
                'modification-coordonnees-bancaire' => ['title' => 'Mon espace don - Mettre à jour ses coordonées bancaires'],
                'mes-informations-personelles' => ['title' => 'Mon espace don - Mes informations personelles'],
                'mes-demandes' => ['title' => 'Mon espace don - Mes demandes'],
                'nous-contacter' => ['title' => 'Mon espace don - Nous contacter'],

            ]

        ]
    ];


    foreach ($pages as $slug => $pageData) {
        if (!get_page_by_path($slug)) {
            $id = wp_insert_post([
                  'post_title'   => $pageData['title'],
                  'post_name'    => $slug,
                  'post_status'  => 'publish',
                  'post_type'    => 'page',
              ]);

            if ($pageData['children']) {
                foreach ($pageData['children'] as $key => $value) {
                    if (!get_page_by_path($key)) {
                        wp_insert_post([
                            'post_title'   => $value['title'],
                            'post_name'    => $key,
                            'post_status'  => 'publish',
                            'post_type'    => 'page',
                            'post_parent' => $id
                        ]);

                    }
                }
            }

        }

    }
}

register_activation_hook(__FILE__, 'aif_donor_space_create_pages');

function aif_donor_space_load_template($template)
{
    $page_slug = get_post_field('post_name', get_queried_object_id());

    $templates_dir = AIF_DONOR_SPACE_PATH . '/templates/';

    $templates_map = [
        'creer-votre-compte' => $templates_dir . 'create-account.php',
        'connectez-vous' => $templates_dir . 'login-user.php',
        'verifier-votre-email' => $templates_dir . '2FA-verification.php',
        'espace-don' => $templates_dir . 'home.php',
        'mes-recus-fiscaux' => $templates_dir . 'taxt-receipt.php',
        'modifier-mon-mot-de-passe' => $templates_dir . 'reset-password.php',
        'mot-de-passe-oublie' => $templates_dir . 'forgotten-password.php',
        'modification-coordonnees-bancaire' => $templates_dir . 'update-iban.php',
        'mes-informations-personelles' => $templates_dir . 'my-personal-informations.php',
        'mes-demandes' => $templates_dir . 'my-demand.php',
        'nous-contacter' => $templates_dir . 'contact.php',
    ];

    if (array_key_exists($page_slug, $templates_map) && file_exists($templates_map[ $page_slug ])) {
        return $templates_map[ $page_slug ];
    }

    return $template;
}
add_filter('template_include', 'aif_donor_space_load_template');

/**
 *  Assets
 */
function aif_donor_space_enqueue_assets()
{

    wp_enqueue_style(
        'aif-donor-space-style',
        AIF_DONOR_SPACE_URL . 'assets/css/style.css',
        array(),
        '1.0'
    );

    wp_enqueue_script(
        'aif-donor-space-script-check-password',
        AIF_DONOR_SPACE_URL . 'assets/js/check-password.js',
        [],
        '1.0'
    );

    wp_enqueue_script(
        'aif-donor-space-display-password',
        AIF_DONOR_SPACE_URL . 'assets/js/display-password.js',
        [

        ],
        '1.0',
        true
    );

    wp_enqueue_script(
        'aif-donor-space-dropdown',
        AIF_DONOR_SPACE_URL . 'assets/js/dropdown.js',
        [

        ],
        '1.0',
        true
    );

    wp_enqueue_script(
        'aif-donor-iban-formatter',
        AIF_DONOR_SPACE_URL . 'assets/js/iban-formatter.js',
        [

        ],
        '1.0',
        true
    );

    wp_enqueue_script(
        'aif-create-duplicate-tax-receipt',
        plugins_url('/assets/js/create-duplicate-tax-receipt-demand.js', __FILE__),
        [],
        '1.0.,0',
        array(
           'in_footer' => false,
        )
    );

    wp_localize_script('aif-create-duplicate-tax-receipt', 'aifDonorSpace', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url()),
    ));

}
add_action('wp_enqueue_scripts', 'aif_donor_space_enqueue_assets');
