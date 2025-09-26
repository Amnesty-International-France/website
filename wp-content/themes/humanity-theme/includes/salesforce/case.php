<?php

declare(strict_types=1);

function post_salesforce_case(array $data)
{
    $url = URL . 'sobjects/Case/';

    return post_salesforce_data($url, $data);
}


function get_salesforce_case(string $email)
{
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Email not valid.', ['status' => 400]);
    }

    $good_format_email_for_query = "'" . addslashes($email) . "'";
    $encoded = urlencode($good_format_email_for_query);

    $url = URL."query/?q=SELECT+Id,Civilite__c,Nom__c,Prenom__c,Email__c+FROM+Case+WHERE+Email__c=$encoded";

    return get_salesforce_data($url);
}
