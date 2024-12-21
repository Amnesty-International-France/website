<?php

function get_salesforce_data($url)
{
    $access_token = get_salesforce_access_token();

    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        exit;
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
    $url = 'services/apexrest/search/v1/' . $email;
    return get_salesforce_data($url);
}

function has_access_to_donation_space($email)
{
    $data = get_salesforce_user_data($email);
    return $data->isDonateur || $data->isMembre;
}
