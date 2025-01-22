<?php

function isValidIBAN($iban)
{
    $iban = str_replace(' ', '', $iban);
    $iban = str_replace('-', '', $iban);


    $ibanPattern = '/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/';
    if (!preg_match($ibanPattern, $iban)) {
        return false;
    }

    $iban = substr($iban, 4) . substr($iban, 0, 4);
    $iban = strtr($iban, array_combine(
        range('A', 'Z'),
        range(10, 35)
    ));

    $checksum = intval(substr($iban, 0, 1));
    for ($i = 1; $i < strlen($iban); $i++) {
        $checksum = ($checksum * 10 + intval(substr($iban, $i, 1))) % 97;
    }
    return $checksum === 1;
}


function create_duplicate_update_IBAN_request($Contact_ID, $IBAN)
{
    $url = 'services/data/v57.0/sobjects/Case';

    $params = [

        "RecordTypeId" => "012060000011IdCAAU",
        "Type_de_demande_AIF__c" => "Changement IBAN",
        "Origin" => "Web",
        "ContactId" => $Contact_ID,
        "Date_de_la_demande__c" => date("Y-m-d"),
        "Code_Marketing_Prestataire__c" => "WB_ESPDON",
        "Nouvel_IBAN__c" => $IBAN
    ];
    return  post_salesforce_data($url, $params);
}
