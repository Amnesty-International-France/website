<?php

function sortByDateProp(array $objects, string $prop): array
{
    $sortedObjects = $objects;

    usort($sortedObjects, function ($a, $b) use ($prop) {
        return   strtotime($b->$prop)   - strtotime($a->$prop) ;
    });

    return $sortedObjects;
}

function groupByYear(array $objects, string $prop): array
{
    $groupedByYear = [];

    foreach ($objects as $object) {
        $year = date('Y', strtotime($object->$prop));
        if (!isset($groupedByYear[$year])) {
            $groupedByYear[$year] = [];
        }
        $groupedByYear[$year][] = $object;
    }

    krsort($groupedByYear);
    return $groupedByYear;
}

function create_duplicate_taxt_receipt_request($Contact_ID, $taxt_receipt_reference)
{
    $url = 'services/data/v57.0/sobjects/Case';

    $params = [

        'RecordTypeId' => '012060000011IdCAAU',
        'Type_de_demande_AIF__c' => 'Envoi duplicata',
        'Origin' => 'Espace Don',
        'ContactId' => $Contact_ID,
        'Date_de_la_demande__c' => date('Y-m-d'),
        'Code_Marketing_Prestataire__c' => 'WB_ESPDON',
        'Identifiant__c' => $taxt_receipt_reference,
    ];
    return  post_salesforce_data_donor_space($url, $params);
}
