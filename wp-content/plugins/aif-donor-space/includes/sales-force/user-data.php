<?php

function get_salesforce_data($url)
{
    $access_token = get_salesforce_access_token();

    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        exit;
    }


    $response = wp_remote_get($_ENV["AIF_SALESFORCE_URL"] . $url, array(
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


function get_salesforce_member_data($email)
{
    $url = 'services/apexrest/search/v1/' . $email;
    return get_salesforce_data($url);
}

function get_salesforce_user_data($ID)
{
    $url = 'services/data/v57.0/sobjects/Contact/' . $ID;
    return get_salesforce_data($url);
}


function has_access_to_donation_space($sf_user)
{
    return $sf_user->isDonateur || $sf_user->isMembre;
}
