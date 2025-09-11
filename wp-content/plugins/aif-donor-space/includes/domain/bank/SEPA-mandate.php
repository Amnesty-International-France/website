<?php

function get_active_sepa_mandate($sepa_mandates)
{

    $actifMandate = null;

    foreach ($sepa_mandates as $mandate) {

        if ($mandate->Statut__c === 'Actif') {
            $actifMandate = $mandate;

        }
    }

    return $actifMandate;

}
