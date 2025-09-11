<?php

function create_contact_request($Contact_ID, $messge, $subject, $active_mandate)
{
    $url = 'services/data/v57.0/sobjects/Case';

    $params = [

        'RecordTypeId' => '01224000000FCTXAA4',
        'Origin' => 'Espace Don',
        'ContactId' => $Contact_ID,
        'Date_de_la_demande__c' => date('Y-m-d'),
        'Code_Marketing_Prestataire__c' => 'WB_ESPDON',
        'Sens_de_la_communication__c' => 'Entrant',
        'Subject' => $subject,
        'Description' => $messge,
        'Mandat_SEPA__c' => $active_mandate,

    ];
    return  post_salesforce_data_donor_space($url, $params);
}
