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
function get_salesforce_user_taxt_reciept($ID)
{
    $url = 'services/data/v57.0/sobjects/Contact/'.$ID.'/Recus_fiscaux__r?fields=Id,Name,IsDeleted,Statut__c,Debut__c,Fin__c,Type_RF__c, Montant_recu__c';
    return get_salesforce_data($url);
}




function store_SF_user_ID($user_id, $user_SF_ID)
{
    update_user_meta($user_id, 'user_SF_ID', $user_SF_ID);
}

function get_SF_user_ID($user_id)
{
    return get_user_meta($user_id, 'user_SF_ID', true);
}
