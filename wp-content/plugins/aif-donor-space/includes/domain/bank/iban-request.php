<?php

/**
 * Durée du verrou posé à l'envoi d'une demande de changement d'IBAN. Il couvre l'appel à Salesforce
 * et le délai avant que le Case apparaisse dans Mes demandes : ensuite, c'est l'état de la demande
 * dans Salesforce qui dit si une demande est en cours.
 */
const AIF_IBAN_REQUEST_LOCK_TTL = 15 * MINUTE_IN_SECONDS;

/** Statuts Espace Don d'une demande terminée (voir page-mes-demandes.php). */
const AIF_CLOSED_DEMAND_STATUSES = ['Traité', 'Rejeté', 'Fermé - Echoué'];

/**
 * Supprime les espaces et met l'IBAN en majuscules pour le comparer ou l'envoyer à Salesforce.
 */
function aif_normalize_iban(string $iban): string
{
    return strtoupper((string) preg_replace('/\s+/', '', $iban));
}

/**
 * Format (code pays, clé, BBAN de 11 à 30 caractères) et clé de contrôle mod-97 d'un IBAN normalisé.
 */
function aif_is_valid_iban(string $iban): bool
{
    if (1 !== preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban)) {
        return false;
    }

    // Les 4 premiers caractères passent à la fin, chaque lettre devient un nombre (A = 10 ... Z = 35).
    $digits = '';
    foreach (str_split(substr($iban, 4) . substr($iban, 0, 4)) as $char) {
        $digits .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
    }

    // Reste de la division par 97, calculé par morceaux : le nombre dépasse les entiers PHP.
    $remainder = 0;
    foreach (str_split($digits, 7) as $chunk) {
        $remainder = (int) ($remainder . $chunk) % 97;
    }

    return 1 === $remainder;
}

/**
 * Une demande « Changement IBAN » du contact est-elle encore ouverte dans Salesforce ?
 *
 * @param array<int, object> $demands Demandes renvoyées par get_salesforce_user_demands().
 */
function aif_has_open_iban_request(array $demands): bool
{
    foreach ($demands as $demand) {
        if (
            'Changement IBAN' === ($demand->Type_de_demande_AIF__c ?? null)
            && !in_array($demand->Statut_Espace_Don__c ?? '', AIF_CLOSED_DEMAND_STATUSES, true)
        ) {
            return true;
        }
    }

    return false;
}

function aif_iban_request_lock_key(string $contact_id): string
{
    return 'aif_iban_request_lock_' . md5($contact_id);
}

/**
 * Pose le verrou de façon atomique ; false si une autre requête le détient déjà.
 *
 * Écrit comme un transient (WordPress purge les transients expirés), mais avec INSERT IGNORE :
 * deux envois simultanés s'appuient sur l'unicité de option_name, pas sur une lecture puis une
 * écriture que les deux pourraient passer. Un verrou expiré est repris par compare-and-swap sur
 * son échéance : une seule requête gagne.
 */
function aif_claim_iban_request(string $contact_id): bool
{
    global $wpdb;

    $key = aif_iban_request_lock_key($contact_id);
    $expires_at = (string) (time() + AIF_IBAN_REQUEST_LOCK_TTL);

    $inserted = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off'), (%s, %s, 'off')",
        '_transient_' . $key,
        '1',
        '_transient_timeout_' . $key,
        $expires_at
    ));
    if (false === $inserted) {
        aif_log_iban_request_db_error('claim');

        return false;
    }
    if (2 === $inserted) {
        return true;
    }

    $current_expiry = aif_get_iban_request_lock_expiry($contact_id);
    if (null === $current_expiry || (int) $current_expiry > time()) {
        return false;
    }

    $retaken = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s",
        $expires_at,
        '_transient_timeout_' . $key,
        $current_expiry
    ));
    if (false === $retaken) {
        aif_log_iban_request_db_error('retake');
    }

    return 1 === $retaken;
}

function aif_is_iban_request_locked(string $contact_id): bool
{
    $expiry = aif_get_iban_request_lock_expiry($contact_id);

    return null !== $expiry && (int) $expiry > time();
}

function aif_release_iban_request(string $contact_id): void
{
    global $wpdb;

    $key = aif_iban_request_lock_key($contact_id);
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name IN (%s, %s)",
        '_transient_' . $key,
        '_transient_timeout_' . $key
    ));
    if (false === $deleted) {
        aif_log_iban_request_db_error('release');
    }
}

function aif_get_iban_request_lock_expiry(string $contact_id): ?string
{
    global $wpdb;

    // Lecture directe de la table : le cache des options masquerait l'écriture d'une autre requête.
    $expiry = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        '_transient_timeout_' . aif_iban_request_lock_key($contact_id)
    ));

    return is_string($expiry) ? $expiry : null;
}

function aif_log_iban_request_db_error(string $step): void
{
    global $wpdb;

    error_log(sprintf('[AIF IBAN] Request lock %s failed: %s', $step, (string) ($wpdb->last_error ?? '')));
}
