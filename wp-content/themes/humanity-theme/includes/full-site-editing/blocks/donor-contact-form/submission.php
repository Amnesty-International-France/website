<?php

declare(strict_types=1);

/** Only the authorized frontend request prepared by auth_my_space() may send. */
function aif_get_donor_contact_form_member(): ?object
{
    if (is_admin() || wp_is_rest_endpoint() || is_preview() || !is_singular() || !is_user_logged_in()) {
        return null;
    }

    // This internal query variable is set after the shared access checks.
    // Rendering content in REST, search or feeds must not repeat those checks.
    $member = get_query_var('aif_salesforce_member');

    return is_object($member) && !is_wp_error($member) ? $member : null;
}

function aif_handle_donor_contact_form(): void
{
    $member = aif_get_donor_contact_form_member();
    if (null === $member) {
        return;
    }

    nocache_headers();
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || ($_POST['aif_donor_contact_form'] ?? null) !== '1') {
        return;
    }

    if (!isset($_POST['contact_nonce']) || !is_string($_POST['contact_nonce']) || !wp_verify_nonce($_POST['contact_nonce'], 'contact_form')) {
        wp_die('Invalid nonce.', '', ['response' => 403]);
    }

    $subject = isset($_POST['subject']) && is_string($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
    $message = isset($_POST['message']) && is_string($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $errors = [];
    if ('' === $subject || mb_strlen($subject) > 255) {
        $errors['subject'] = 'Indiquez un objet de 1 à 255 caractères.';
    }
    if ('' === $message) {
        $errors['message'] = 'Indiquez le message de votre demande.';
    }

    $state = [
        'subject' => $subject,
        'message' => $message,
        'errors' => $errors,
        'error_message' => $errors ? 'Veuillez vérifier les champs du formulaire.' : '',
    ];
    set_query_var('aif_donor_contact_form', $state);
    if ($errors) {
        return;
    }

    $token = isset($_POST['submission_token']) && is_string($_POST['submission_token']) ? sanitize_text_field(wp_unslash($_POST['submission_token'])) : '';
    if (!wp_is_uuid($token, 4)) {
        $state['error_message'] = 'Le formulaire a expiré. Veuillez renvoyer votre demande.';
        set_query_var('aif_donor_contact_form', $state);
        return;
    }

    $contact = aif_require_salesforce_object(get_salesforce_user_data($member->Id), 'contact', 'Id');

    // Same displayed form sent again (repeated clicks): never create a second Case,
    // answer with the outcome of the first submission instead.
    $submission_key = aif_get_donor_contact_submission_key(get_current_user_id(), $token);
    if (!aif_claim_donor_contact_submission($submission_key)) {
        aif_respond_to_duplicate_donor_contact_submission($submission_key, $state);
        return;
    }

    $result = create_contact_request($member->Id, $message, $subject, $contact->Tech_Lien_Mandat_Actif__c ?? null);
    if (is_wp_error($result)) {
        aif_set_donor_contact_submission_status($submission_key, 'failed');
        aif_salesforce_service_unavailable($result);
    }
    if (is_object($result) && !empty($result->success)) {
        aif_set_donor_contact_submission_status($submission_key, 'sent');
        aif_redirect_to_donor_contact_success();
    }

    aif_set_donor_contact_submission_status($submission_key, 'failed');
    $state['error_message'] = "Votre demande n'a pas pu aboutir. Veuillez réessayer plus tard.";
    set_query_var('aif_donor_contact_form', $state);
}

function aif_redirect_to_donor_contact_success(): never
{
    wp_safe_redirect(add_query_arg('contact_sent', '1', get_permalink(get_queried_object_id())), 303);
    exit;
}

function aif_create_donor_contact_submission_token(): string
{
    return wp_generate_uuid4();
}

function aif_get_donor_contact_submission_key(int $user_id, string $token): string
{
    return 'aif_contact_submission_' . md5($user_id . '|' . strtolower($token));
}

/**
 * Atomically marks a submission as in progress; false when it was already claimed.
 *
 * Stored as a transient so WordPress purges it once expired, but written with
 * INSERT IGNORE: concurrent requests rely on the unique option_name, not on a
 * read-then-write that two requests could both pass.
 */
function aif_claim_donor_contact_submission(string $key): bool
{
    global $wpdb;

    $inserted = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
        '_transient_' . $key,
        'pending'
    ));
    if (1 !== $inserted) {
        return false;
    }

    $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')",
        '_transient_timeout_' . $key,
        (string) (time() + DAY_IN_SECONDS)
    ));

    return true;
}

function aif_set_donor_contact_submission_status(string $key, string $status): void
{
    global $wpdb;

    $wpdb->update($wpdb->options, ['option_value' => $status], ['option_name' => '_transient_' . $key]);
}

function aif_get_donor_contact_submission_status(string $key): ?string
{
    global $wpdb;

    // Read the table directly: the options cache would hide the first request's update.
    $status = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        '_transient_' . $key
    ));

    return is_string($status) ? $status : null;
}

/** @param array<string, mixed> $state */
function aif_respond_to_duplicate_donor_contact_submission(string $key, array $state): void
{
    // The browser only shows the response to the last click: wait for the first
    // request (Salesforce timeout is 30 s) so this one can report its outcome.
    $deadline = time() + 35;
    $status = aif_get_donor_contact_submission_status($key);
    while ('pending' === $status && time() < $deadline) {
        sleep(1);
        $status = aif_get_donor_contact_submission_status($key);
    }

    if ('sent' === $status) {
        aif_redirect_to_donor_contact_success();
    }

    $state['error_message'] = 'pending' === $status
        ? 'Votre demande est en cours de traitement. Consultez la page Mes demandes avant de la renvoyer.'
        : "Votre demande n'a pas pu aboutir. Veuillez réessayer plus tard.";
    set_query_var('aif_donor_contact_form', $state);
}
