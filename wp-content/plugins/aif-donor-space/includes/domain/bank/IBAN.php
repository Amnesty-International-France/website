<?php

function create_duplicate_update_IBAN_request($Contact_ID, $IBAN)
{
    $url = 'services/data/v57.0/sobjects/Case';

    $params = [

        "RecordTypeId" => "012060000011IdCAAU",
        "Type_de_demande_AIF__c" => "Changement IBAN",
        "Origin" => "Espace Don",
        "ContactId" => $Contact_ID,
        "Date_de_la_demande__c" => date("Y-m-d"),
        "Code_Marketing_Prestataire__c" => "WB_ESPDON",
        "Nouvel_IBAN__c" => $IBAN
    ];
    return  post_salesforce_data($url, $params);
}
