<?php

require_once __DIR__ . '/errors.php';

function post_salesforce_data_donor_space($url, $params = [])
{
    $started_at = microtime(true);
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
    $response = wp_remote_post($aif_salesforce_base_url . $url, [
        'method'    => 'POST',
        'body'      => json_encode($params),
        'timeout'   => 30,
        'headers'   => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    return aif_parse_salesforce_response($response, 'POST', $url, $started_at);
}

function patch_salesforce_data_donor_space($url, $params = [])
{
    $started_at = microtime(true);
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
    $response = wp_remote_request($aif_salesforce_base_url . $url, [
        'method'    => 'PATCH',
        'body'      => json_encode($params),
        'timeout'   => 30,
        'headers'   => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    return aif_parse_salesforce_response($response, 'PATCH', $url, $started_at, true);
}


function get_salesforce_data_donor_space($url)
{
    $started_at = microtime(true);
    $access_token = get_salesforce_access_token_donor_space_donor_space();

    if (is_wp_error($access_token)) {
        return $access_token;
    }

    $aif_salesforce_base_url = defined('AIF_SALESFORCE_URL') ? AIF_SALESFORCE_URL : getenv('AIF_SALESFORCE_URL');
    $response = wp_remote_get($aif_salesforce_base_url . $url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'timeout' => 30,
    ]);

    return aif_parse_salesforce_response($response, 'GET', $url, $started_at);
}


function get_salesforce_member_data($email)
{
    $url = 'services/apexrest/search/v1/' . $email;
    return get_salesforce_data_donor_space($url);
}

function get_salesforce_user_data($ID)
{
    if (empty($ID)) {
        return aif_create_salesforce_missing_identifier_error('GET', 'contact');
    }

    $url = 'services/data/v57.0/sobjects/Contact/' . $ID;
    return get_salesforce_data_donor_space($url);
}

function patch_salesforce_user_data($userData, $ID)
{
    if (empty($ID)) {
        return aif_create_salesforce_missing_identifier_error('PATCH', 'contact');
    }

    $url = 'services/data/v57.0/sobjects/Contact/' . $ID;
    return patch_salesforce_data_donor_space($url, $userData);
}



function has_access_to_donation_space($sf_user)
{
    if (is_wp_error($sf_user) || !is_object($sf_user)) {
        return false;
    }

    return !empty($sf_user->isDonateur) || !empty($sf_user->isMembre);
}
function get_salesforce_user_tax_reciept($ID)
{
    if (empty($ID)) {
        return aif_create_salesforce_missing_identifier_error('GET', 'tax_receipts');
    }

    $url = '/services/apexrest/retrieve/v1/RecuFiscaux/?idContact='.$ID;
    return get_salesforce_data_donor_space($url);
}

function get_salesforce_user_SEPA_mandate($ID)
{
    if (empty($ID)) {
        return aif_create_salesforce_missing_identifier_error('GET', 'sepa_mandates');
    }

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
    if (true === $sf_user->isMembre) {
        return 'membre';
    }

    if (true === $sf_user->isDonateur) {
        return 'donateur';
    }

    return '';
}


function get_salesforce_user_demands($ID)
{
    if (empty($ID)) {
        return aif_create_salesforce_missing_identifier_error('GET', 'demands');
    }

    $url = '/services/apexrest/retrieve/v1/Demandes/?idContact=' . $ID;
    return get_salesforce_data_donor_space($url);
}
