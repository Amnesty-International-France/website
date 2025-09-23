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

define('AIF_DONOR_SPACE_PATH', plugin_dir_path(__FILE__));
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
require_once AIF_DONOR_SPACE_PATH. '/includes/domain/contact/index.php';

/*
/  Utils
*/
require_once AIF_DONOR_SPACE_PATH. '/includes/utils.php';

/*
/ Configure Plugin
*/

function aif_create_pages_recursively($pages, $parent_id = 0, $parent_path = '')
{
    foreach ($pages as $slug => $pageData) {
        $current_path = $parent_path ? "{$parent_path}/{$slug}" : $slug;
        $existing_page = get_page_by_path($current_path);
        $new_page_id = 0;

        if (!$existing_page) {
            $new_page_id = wp_insert_post([
                'post_title'   => $pageData['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_parent'  => $parent_id,
            ]);
        } else {
            $new_page_id = $existing_page->ID;
        }

        if ($new_page_id > 0 && !empty($pageData['children']) && is_array($pageData['children'])) {
            aif_create_pages_recursively($pageData['children'], $new_page_id, $current_path);
        }
    }
}

function aif_donor_space_create_pages()
{
    $pages = [
        'connectez-vous' => [
            'title' => 'Connectez-vous',
        ],
        'creer-votre-compte' => [
            'title' => 'Créer votre compte',
        ],
        'verifier-votre-email' => [
            'title' => 'Vérifier votre email',
        ],
        'mot-de-passe-oublie' => [
            'title' => 'Mot de passe oublié',
        ],
        'mon-espace' => [
            'title' => 'Mon Espace',
            'children' => [
                'actualites' => ['title' => 'Actualités'],
                'agir-et-se-mobiliser' => ['title' => 'Agir et se mobiliser'],
                'vie-democratique' => [
                    'title' => 'Vie démocratique',
                    'children' => [
                        'ressources-vie-democratique' => ['title' => 'Ressources vie démocratique'],
                    ],
                ],
                'boite-a-outils' => [
                    'title' => 'Boite outils',
                    'children' => [
                        'ressources-militants' => ['title' => 'Ressources militants'],
                    ],
                ],
                'mes-dons' => [
                    'title' => 'Mes dons',
                    'children' => [
                        'mes-informations-personnelles' => ['title' => 'Mes informations personnelles'],
                        'mes-recus-fiscaux' => ['title' => 'Mes Reçus Fiscaux'],
                        'mes-demandes' => ['title' => 'Mes Demandes'],
                        'nous-contacter' => ['title' => 'Nous Contacter'],
                    ],
                ],
                'mon-compte' => [
                    'title' => 'Mon compte',
                    'children' => [
                        'se-deconnecter' => ['title' => 'Se déconnecter'],
                    ],
                ],
            ],
        ],
    ];

    aif_create_pages_recursively($pages);
}

function aif_ensure_critical_pages_exist()
{
    if (false === get_transient('aif_critical_pages_check_lock')) {
        aif_donor_space_create_pages();
        set_transient('aif_critical_pages_check_lock', 'true', 5 * MINUTE_IN_SECONDS);
    }
}

add_action('init', 'aif_ensure_critical_pages_exist');

add_action('after_switch_theme', 'aif_donor_space_create_pages');

/**
 *  Assets
 */
function aif_donor_space_enqueue_assets()
{

    wp_enqueue_style(
        'aif-donor-space-style',
        AIF_DONOR_SPACE_URL . 'assets/css/style.css',
        [],
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
        ['jquery'],
        '1.0',
        true
    );

    wp_enqueue_script(
        'aif-donor-iban-formatter',
        AIF_DONOR_SPACE_URL . 'assets/js/iban-formatter.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_enqueue_script(
        'aif-create-duplicate-tax-receipt',
        plugins_url('/assets/js/create-duplicate-tax-receipt-demand.js', __FILE__),
        [],
        '1.0.0',
        [
            'in_footer' => false,
        ]
    );

    wp_localize_script('aif-create-duplicate-tax-receipt', 'aifDonorSpace', [
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url()),
    ]);

}
add_action('wp_enqueue_scripts', 'aif_donor_space_enqueue_assets');
