<?php

/**
 * Durée maximale du verrou posé à l'envoi d'une demande de changement d'IBAN. Il couvre l'appel à
 * Salesforce et le délai avant que le Case apparaisse dans Mes demandes : il est levé dès que le Case
 * y apparaît, et c'est alors l'état de la demande dans Salesforce qui dit si elle est en cours.
 */
const AIF_IBAN_REQUEST_LOCK_TTL = 15 * MINUTE_IN_SECONDS;

const AIF_IBAN_REQUEST_TYPE = 'Changement IBAN';

/**
 * Statut Espace Don d'une demande en cours (voir page-mes-demandes.php). Un statut inconnu ne bloque
 * pas : mieux vaut risquer un doublon que bloquer un donateur pour toujours sur un Case jamais clos.
 */
const AIF_OPEN_DEMAND_STATUSES = ['En cours de traitement'];

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
    foreach (aif_filter_iban_requests($demands) as $demand) {
        if (in_array($demand->Statut_Espace_Don__c ?? '', AIF_OPEN_DEMAND_STATUSES, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Nombre de demandes « Changement IBAN » du contact dans Salesforce, quel que soit leur statut.
 * Gardé dans le verrou : quand il augmente, le Case envoyé est visible dans Salesforce.
 *
 * @param array<int, object> $demands Demandes renvoyées par get_salesforce_user_demands().
 */
function aif_count_iban_requests(array $demands): int
{
    return count(aif_filter_iban_requests($demands));
}

/**
 * @param array<int, object> $demands
 * @return array<int, object>
 */
function aif_filter_iban_requests(array $demands): array
{
    return array_filter(
        $demands,
        static fn ($demand): bool => is_object($demand) && AIF_IBAN_REQUEST_TYPE === ($demand->Type_de_demande_AIF__c ?? null)
    );
}

function aif_iban_request_lock_key(string $contact_id): string
{
    return 'aif_iban_request_lock_' . md5($contact_id);
}

/**
 * Pose le verrou de façon atomique ; false si une autre requête le détient déjà.
 * Le verrou garde le nombre de demandes « Changement IBAN » connues au moment de l'envoi.
 *
 * Écrit comme un transient (WordPress purge les transients expirés), mais avec INSERT IGNORE :
 * deux envois simultanés s'appuient sur l'unicité de option_name, pas sur une lecture puis une
 * écriture que les deux pourraient passer. Un verrou expiré est repris par compare-and-swap sur
 * son échéance : une seule requête gagne.
 */
function aif_claim_iban_request_lock(string $contact_id, int $known_requests): bool
{
    global $wpdb;

    $key = aif_iban_request_lock_key($contact_id);
    $expires_at = (string) (time() + AIF_IBAN_REQUEST_LOCK_TTL);

    $inserted = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off'), (%s, %s, 'off')",
        '_transient_' . $key,
        (string) $known_requests,
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
    // Une seule ligne insérée : l'échéance manquait et c'est la nôtre qui a été écrite.
    if (1 === $inserted && $expires_at === $current_expiry) {
        aif_set_iban_request_lock_count($key, $known_requests);

        return true;
    }
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
    if (1 !== $retaken) {
        return false;
    }

    aif_set_iban_request_lock_count($key, $known_requests);

    return true;
}

function aif_set_iban_request_lock_count(string $key, int $known_requests): void
{
    global $wpdb;

    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s",
        (string) $known_requests,
        '_transient_' . $key
    ));
    if (false === $updated) {
        aif_log_iban_request_db_error('count');
    }
}

/**
 * Nombre de demandes « Changement IBAN » connues à l'envoi, ou null sans verrou.
 */
function aif_get_iban_request_lock_count(string $contact_id): ?int
{
    global $wpdb;

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        '_transient_' . aif_iban_request_lock_key($contact_id)
    ));

    return is_numeric($count) ? (int) $count : null;
}

function aif_is_iban_request_locked(string $contact_id): bool
{
    $expiry = aif_get_iban_request_lock_expiry($contact_id);

    return null !== $expiry && (int) $expiry > time();
}

function aif_release_iban_request_lock(string $contact_id): void
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
