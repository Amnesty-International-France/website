<?php

/**
 * Supprime les espaces et met l'IBAN en majuscules pour le comparer ou l'envoyer à Salesforce.
 */
function aif_normalize_iban(string $iban): string
{
    return strtoupper((string) preg_replace('/\s+/', '', $iban));
}

/**
 * Clé du transient qui bloque une nouvelle demande de changement d'IBAN tant que la précédente est en cours.
 */
function aif_iban_request_pending_key(string $contact_id): string
{
    return 'aif_iban_request_pending_' . $contact_id;
}

function create_duplicate_update_IBAN_request($Contact_ID, $IBAN)
{
    $url = 'services/data/v57.0/sobjects/Case';

    $params = [

        'RecordTypeId' => '012060000011IdCAAU',
        'Type_de_demande_AIF__c' => 'Changement IBAN',
        'Origin' => 'Espace Don',
        'ContactId' => $Contact_ID,
        'Date_de_la_demande__c' => date('Y-m-d'),
        'Code_Marketing_Prestataire__c' => 'WB_ESPDON',
        'Nouvel_IBAN__c' => $IBAN,
    ];
    return  post_salesforce_data_donor_space($url, $params);
}
