<?php

declare(strict_types=1);

function render_donor_contact_form_block(): string
{
    // Frontend previews, editor preloads and ServerSideRender have no prepared
    // donor context. They must not read donor details or submit a Case.
    $is_editor = is_admin() && function_exists('get_current_screen') && get_current_screen()?->is_block_editor();
    $is_rest_editor = wp_is_serving_rest_request() && 'edit' === ($_GET['context'] ?? '');
    $preview_post_id = ($is_editor || $is_rest_editor) ? get_the_ID() : get_queried_object_id();
    // autoRegister omits post_id. Match the native block-renderer permission
    // for this static placeholder; a supplied page still requires edit_post.
    $can_preview = $preview_post_id ? current_user_can('edit_post', $preview_post_id) : ($is_rest_editor && current_user_can('edit_posts'));
    if (($is_editor || $is_rest_editor || is_preview()) && $can_preview) {
        return '<section class="aif-container--form"><h1>Nous contacter</h1><p>Le formulaire est disponible dans votre espace donateur.</p></section>';
    }

    if (!is_user_logged_in() || aif_is_unverified_subscriber(wp_get_current_user())) {
        wp_die('Vous ne pouvez pas accéder à ce formulaire.', '', ['response' => 403]);
    }

    // Rendering needs the member prepared by template_redirect. Unlike pattern
    // enumeration, a missing context here is still a real service failure.
    $sf_member = aif_validate_salesforce_member(aif_get_request_salesforce_member());
    if (is_wp_error($sf_member)) {
        aif_salesforce_service_unavailable($sf_member);
    }
    if (aif_is_salesforce_contact_absent($sf_member) || !has_access_to_donation_space($sf_member)) {
        wp_die('Vous ne pouvez pas accéder à ce formulaire.', '', ['response' => 403]);
    }

    $is_submission = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'], $_POST['message']);
    if ($is_submission && (!isset($_POST['contact_nonce']) || !is_string($_POST['contact_nonce']) || !wp_verify_nonce($_POST['contact_nonce'], 'contact_form'))) {
        wp_die('Invalid nonce.', '', ['response' => 403]);
    }

    if (!headers_sent()) {
        header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    $sf_user = aif_require_salesforce_object(get_salesforce_user_data($sf_member->Id), 'contact', 'Id');
    $subject = isset($_GET['subject']) && is_string($_GET['subject']) ? sanitize_text_field(wp_unslash($_GET['subject'])) : '';
    $success_message = '';
    $error_message = '';

    if ($is_submission) {
        $subject = sanitize_text_field(wp_unslash($_POST['subject']));
        $message = sanitize_text_field(wp_unslash($_POST['message']));
        $request_result = aif_submit_donor_contact_form_once($sf_member->Id, $message, $subject, $sf_user->Tech_Lien_Mandat_Actif__c ?? null);

        if (is_wp_error($request_result)) {
            aif_salesforce_service_unavailable($request_result);
        }
        if (is_object($request_result) && !empty($request_result->success)) {
            $success_message_title = 'Votre demande de contact à bien été prise en compte';
            $url = get_permalink(get_page_by_path('mes-demandes'));
            $success_message = "Vous pouvez voir le suivi du traitement de vos demandes sur la page  <a class='aif-link--secondary' href='{$url}'> Mes demandes. </a>";
        } else {
            $error_message = "Votre demande n'a pas pu aboutir. Veuillez réessayer plus tard.";
        }
    }

    ob_start();
    try {
        require dirname(__DIR__, 4) . '/partials/my-space/contact-form.php';

        return (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }
}

/** One POST must not create another Case when WordPress renders a block twice. */
function aif_submit_donor_contact_form_once($contact_id, $message, $subject, $active_mandate)
{
    static $submitted = false;
    static $result;

    if (!$submitted) {
        $submitted = true;
        $result = create_contact_request($contact_id, $message, $subject, $active_mandate);
    }

    return $result;
}
