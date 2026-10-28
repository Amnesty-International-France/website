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

/**
 * Whether the reference matches the exact Name of one of the contact's tax receipts.
 * Returns a WP_Error when the receipts cannot be fetched, so callers fail closed.
 */
function aif_contact_owns_tax_receipt($contact_id, $tax_receipt_reference)
{
    $receipts = get_salesforce_user_tax_reciept($contact_id);

    if (is_wp_error($receipts)) {
        return $receipts;
    }

    if (!is_array($receipts)) {
        return aif_create_salesforce_invalid_response_error('GET', 'tax_receipts');
    }

    foreach ($receipts as $receipt) {
        if (is_object($receipt) && isset($receipt->Name) && $receipt->Name === $tax_receipt_reference) {
            return true;
        }
    }

    return false;
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

function aif_duplicate_tax_receipt_request_key($contact_id, $tax_receipt_reference)
{
    return 'aif_duplicate_tax_receipt_request_' . md5($contact_id . '|' . $tax_receipt_reference);
}

function aif_duplicate_tax_receipt_requests_count_key($contact_id)
{
    return 'aif_duplicate_tax_receipt_requests_count_' . md5($contact_id) . '_' . date('Y-m-d');
}
