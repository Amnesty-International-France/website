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

define("AIF_PLUGIN_PATH", plugin_dir_path(__FILE__));


/*
/ Includes
*/

/*
/ Configure Plugin
*/
require_once plugin_dir_path(__FILE__) . '/includes/plugin/configure.php';


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
/  Domain
*/
require_once plugin_dir_path(__FILE__) . '/includes/domain/tax-receipt.php';


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

    wp_enqueue_script(
        'aif-create-duplicate-tax-receipt',
        plugins_url('/assets/js/create-duplicate-tax-receipt-demand.js', __FILE__),
        [],
        '1.0.,0',
        array(
           'in_footer' => false,
        )
    );

    wp_enqueue_script('aif-donor-space-script', plugin_dir_url(__FILE__) . 'assets/js/create-duplicate-tax-receipt-demand.js', array(), null, true);
    wp_localize_script('aif-donor-space-script', 'aifDonorSpace', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'root' => esc_url_raw(rest_url()),
    ));

}
add_action('wp_enqueue_scripts', 'aif_donor_space_enqueue_assets');
add_action('rest_api_init', function () {
    register_rest_route('aif-donor-space/v1', '/duplicate-tax-receipt-request/', array(
        'methods' => 'POST',
        'callback' => 'handle_duplicate_tax_receipt_request',
        'permission_callback' => 'check_nonce',
    ));
});

function handle_duplicate_tax_receipt_request(WP_REST_Request $request)
{

    $params = $request->get_json_params();

    $userID = get_current_user_id();
    $SF_ID = get_SF_user_ID($userID);
    $taxt_receipt_reference = $params['taxReceiptReference'];

    if (!$SF_ID || !$taxt_receipt_reference) {
        return new WP_REST_Response(array('status' => 403,'message' => 'tax receipt ID not provided'));
    }



    $result = create_duplicate_taxt_receipt_request($SF_ID, $taxt_receipt_reference);

    // if (!$result['success']) {
    //     return new WP_REST_Response(['message' => 'demand failed'], status: 400);
    // }

    $response = array(
        'message' => 'Tax receipt duplicated successfully!',
        'result' => $result
    );
    return new WP_REST_Response($response, 200);
}

function check_nonce(WP_REST_Request $request)
{

    $nonce = $request->get_header('X-WP-Nonce');

    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Invalid nonce.', array('status' => 403));
    }

    if (!is_user_logged_in()) {
        return new WP_Error('rest_forbidden', 'Not logged in.', array('status' => 403));

    }


    return true;
}
