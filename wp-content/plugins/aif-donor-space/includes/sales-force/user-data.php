<?php


function post_salesforce_data_donor_space($url, $params = [])
{
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        exit;
    }

	$aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
	$response = wp_remote_post($aif_salesforce_base_url . $url, array(
        'method'    => 'POST',
        'body'      => json_encode($params),
        'timeout'   => 15,
        'headers'   => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ),
    ));

    if (is_wp_error($response)) {
        echo 'Erreur de requête Salesforce : ' . $response->get_error_message();
        return false;
    } else {
        $data = wp_remote_retrieve_body($response);
        return json_decode($data);
    }
}

function patch_salesforce_data_donor_space($url, $params = [])
{
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        exit;
    }

	$aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
	$response = wp_remote_request($aif_salesforce_base_url . $url, array(
        'method'    => 'PATCH',
        'body'      => json_encode($params),
        'timeout'   => 15,
        'headers'   => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ),
    ));

    if (is_wp_error($response)) {
        echo 'Erreur de requête Salesforce : ' . $response->get_error_message();
        return false;
    } else {
        $data = wp_remote_retrieve_body($response);
        return json_decode($data);
    }
}


function get_salesforce_data_donor_space($url)
{
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        echo 'Erreur : ' . $access_token->get_error_message();
        exit;
    }

	$aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
    $response = wp_remote_get($aif_salesforce_base_url . $url, array(
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
    return get_salesforce_data_donor_space($url);
}

function get_salesforce_user_data($ID)
{
    $url = 'services/data/v57.0/sobjects/Contact/' . $ID;
    return get_salesforce_data_donor_space($url);
}

function patch_salesforce_user_data($userData, $ID)
{
    $url = 'services/data/v57.0/sobjects/Contact/' . $ID;
    return patch_salesforce_data_donor_space($url, $userData);
}



function has_access_to_donation_space($sf_user)
{
    return $sf_user->isDonateur || $sf_user->isMembre;
}
function get_salesforce_user_tax_reciept($ID)
{
    $url = '/services/apexrest/retrieve/v1/RecuFiscaux/?idContact='.$ID;
    return get_salesforce_data_donor_space($url);
}

function get_salesforce_user_SEPA_mandate($ID)
{
    $url = 'services/data/v57.0/sobjects/Contact/'.$ID.'/Mandats_SEPA__r?fields=Id,Name,RUM__c,Montant__c,Statut__c,Periodicite__c,Date_paiement_Avenir__c,Tech_Iban__c';
    return get_salesforce_data_donor_space($url);
}




function store_SF_user_ID($user_id, $user_SF_ID)
{
    update_user_meta($user_id, 'user_SF_ID', $user_SF_ID);
}

function get_SF_user_ID($user_id)
{
    return get_user_meta($user_id, 'user_SF_ID', true);
}

function store_email_token($user_id, $token)
{
    update_user_meta($user_id, 'user_email_token', $token);
}


function get_email_token($user_id)
{
    return get_user_meta($user_id, 'user_email_token', true);
}




function aif_get_user_status($sf_user)
{

    if ($sf_user->isMembre == true) {
        return "membre";
    }

    if ($sf_user->isDonateur == true) {
        return "donateur";
    }
}


function get_salesforce_user_demands($ID)
{
    $url = '/services/apexrest/retrieve/v1/Demandes/?idContact=' . $ID;
    return get_salesforce_data_donor_space($url);
}
