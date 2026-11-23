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
