<?php

/**
 * Espace-donateur Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package espace-donateur
 */


/*
/ Configure Child Theme
*/

add_action('wp_enqueue_scripts', 'humanity_theme_parent_theme_enqueue_styles');

function humanity_theme_parent_theme_enqueue_styles()
{
    wp_enqueue_style('humanity-theme-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'espace-donateur-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['humanity-theme-style']
    );
}

/*
/ Sales Force
*/
function get_salesforce_access_token()
{
    // Vérifie si un token d'accès valide est déjà stocké
    $access_token = get_option('salesforce_access_token');
    $expiration_time = get_option('salesforce_token_expiration_time');

    // Si le token est encore valide, le retourner
    if ($access_token && $expiration_time && time() < $expiration_time) {
        return $access_token;
    }

    // Sinon, il faut rafraîchir le token
    return refresh_salesforce_token();
}

function refresh_salesforce_token()
{
    // Paramètres de la requête pour rafraîchir le token
    $client_id = SALESFORCE_CLIENT_ID;         // Ton Consumer Key (client_id Salesforce)
    $client_secret = SALESFORCE_SECRET; // Ton Consumer Secret (client_secret Salesforce)

    // URL de l'API Salesforce (URL de production)
    $url = SALESFORCE_URL . 'services/oauth2/token';


    // Paramètres pour la requête POST
    $params = array(
        'grant_type'    => 'client_credentials',
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
    );

    // Effectuer la requête POST via wp_remote_post()
    $response = wp_remote_post($url, array(
        'method'    => 'POST',
        'body'      => $params,
        'timeout'   => 15,
        'headers'   => array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        ),
    ));


    // Vérifier si la requête a réussi
    if (is_wp_error($response)) {
        return new WP_Error('request_failed', 'La requête a échoué', $response->get_error_message());
    }

    // Analyser la réponse JSON
    $body = wp_remote_retrieve_body($response);


    $data = json_decode($body, true);

    // Si l'authentification a réussi, on obtient un nouveau access_token
    if (isset($data['access_token'])) {
        // Sauvegarder le nouveau token et sa date d'expiration
        update_option('salesforce_access_token', $data['access_token']);
        update_option('salesforce_token_expiration_time', time() + $data['issued_at']); // La durée d'expiration est donnée en secondes

        // Retourner le nouveau access_token
        return $data['access_token'];
    } else {
        return new WP_Error('token_refresh_failed', 'Le rafraîchissement du token a échoué', $data);
    }
}



function get_salesforce_data($url)
{

    $access_token = get_salesforce_access_token();


    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        return;
    }


    $response = wp_remote_get(SALESFORCE_URL . $url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token
        )
    ));


    if (is_wp_error($response)) {
        echo 'Erreur de requête Salesforce : ' . $response->get_error_message();
    } else {
        $data = wp_remote_retrieve_body($response);
        return json_decode($data);
    }
}


function get_salesforce_user_data($email)
{
    // L'URL pour récupérer les données
    $url = 'apexrest/search/v1/' . $email;

    return get_salesforce_data($url);
}
