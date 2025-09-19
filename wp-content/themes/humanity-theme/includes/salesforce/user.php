<?php

const URL = 'services/data/v57.0/';
const QUERY = 'query/?q=SELECT+Id,Salutation,FirstName,LastName,Email,Code_Postal__c,Pays__c,MobilePhone+FROM+Contact';

function get_salesforce_users()
{
    $url = URL.QUERY;
    return get_salesforce_data($url);
}

function get_salesforce_users_query($query)
{
    return get_salesforce_data($query);
}

function get_salesforce_user_with_email(string $email)
{
    $good_format_email_for_query = "'" . addslashes($email) . "'";
    $encoded = urlencode($good_format_email_for_query);

    $url = URL.QUERY."+WHERE+Email=$encoded";
    return get_salesforce_data($url);
}

function post_salesforce_users(array $data)
{
    $post_url = URL . 'sobjects/Contact/';

    return post_salesforce_data($post_url, $data);
}

function post_salesforce_activist(array $data)
{
    $post_url = URL . 'sobjects/Militant__c/';

    return post_salesforce_data($post_url, $data);
}

function update_salesforce_users(string $user_id, array $data)
{
    $post_url = URL . "sobjects/Contact/Ext_ID_Contact/$user_id";

    return patch_salesforce_data($post_url, $data);
}
