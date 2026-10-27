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

    $contact = aif_require_salesforce_object(get_salesforce_user_data($member->Id), 'contact', 'Id');
    $result = create_contact_request($member->Id, $message, $subject, $contact->Tech_Lien_Mandat_Actif__c ?? null);
    if (is_wp_error($result)) {
        aif_salesforce_service_unavailable($result);
    }
    if (is_object($result) && !empty($result->success)) {
        wp_safe_redirect(add_query_arg('contact_sent', '1', get_permalink(get_queried_object_id())), 303);
        exit;
    }

    $state['error_message'] = "Votre demande n'a pas pu aboutir. Veuillez réessayer plus tard.";
    set_query_var('aif_donor_contact_form', $state);
}
