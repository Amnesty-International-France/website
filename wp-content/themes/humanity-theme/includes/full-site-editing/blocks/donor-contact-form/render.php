<?php

declare(strict_types=1);

function render_donor_contact_form_block(): string
{
    // wp_is_rest_endpoint() also covers internal editor preloads.
    if (is_admin() || wp_is_rest_endpoint() || is_preview()) {
        $post_id = is_preview() ? get_queried_object_id() : get_the_ID();
        $can_preview = $post_id ? current_user_can('edit_post', $post_id) : current_user_can('edit_posts');

        return $can_preview ? '<section class="aif-container--form"><h1>Nous contacter</h1><p>Le formulaire est disponible dans votre espace donateur.</p></section>' : '';
    }

    if (null === aif_get_donor_contact_form_member()) {
        return '';
    }

    $state = get_query_var('aif_donor_contact_form', []);
    $subject = $state['subject'] ?? (isset($_GET['subject']) && is_string($_GET['subject']) ? sanitize_text_field(wp_unslash($_GET['subject'])) : '');
    $message = $state['message'] ?? '';
    $errors = $state['errors'] ?? [];
    $error_message = $state['error_message'] ?? '';
    $success_message = '';
    if ('1' === ($_GET['contact_sent'] ?? null) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        $success_message_title = 'Votre demande de contact a bien été prise en compte';
        $requests_page = get_page_by_path('mon-espace/mes-dons/mes-demandes');
        $success_message = 'Votre demande a été transmise.';
        if ($requests_page) {
            $url = esc_url(get_permalink($requests_page));
            $success_message = "Vous pouvez suivre le traitement de vos demandes sur la page <a class='aif-link--secondary' href='{$url}'>Mes demandes</a>.";
        }
    }

    $template = locate_template('partials/my-space/contact-form.php');
    if (!$template) {
        return '';
    }

    ob_start();
    try {
        require $template;

        return (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }
}
