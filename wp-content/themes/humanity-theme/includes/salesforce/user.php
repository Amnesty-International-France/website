<?php

function get_salesforce_users()
{
    $url = 'services/data/v57.0/query/?q=SELECT+Salutation,FirstName,LastName,Email,Code_Postal__c,Pays__c,MobilePhone+FROM+Contact';
    return get_salesforce_data($url);
}

function get_salesforce_users_query($query)
{
    return get_salesforce_data($query);
}
