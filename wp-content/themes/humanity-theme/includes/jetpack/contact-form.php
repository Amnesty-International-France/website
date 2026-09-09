<?php

/**
 * Add the Turnstile widget to every frontend Jetpack form.
 *
 * The filter is applied by Jetpack Forms to both block and shortcode output,
 * including forms whose content is stored in WordPress rather than in the
 * theme repository.
 *
 * @param string $html The rendered Jetpack form HTML.
 * @return string The rendered form with a Turnstile widget.
 */
function aif_add_turnstile_to_jetpack_form(string $html): string
{
    $site_key = aif_turnstile_site_key();

    if (!$site_key || !str_contains($html, '<form')) {
        return $html;
    }

    $widget = sprintf(
        '<div
            class="cf-turnstile"
            data-callback="aifTurnstileSuccess"
            data-error-callback="aifTurnstileFailure"
            data-appearance="interaction-only"
            data-expired-callback="aifTurnstileFailure"
            data-timeout-callback="aifTurnstileFailure"
            data-unsupported-callback="aifTurnstileFailure"
            data-sitekey="%s"
        ></div>',
        esc_attr($site_key)
    );

    $form_with_widget = preg_replace('/(<form\b[^>]*>)/i', '$1' . $widget, $html, 1);

    return is_string($form_with_widget) ? $form_with_widget : $html;
}

add_filter('jetpack_contact_form_html', 'aif_add_turnstile_to_jetpack_form');

/**
 * Reject Jetpack form submissions before Jetpack saves feedback or sends mail.
 *
 * @param bool $is_spam Whether Jetpack already identified the submission as spam.
 * @return bool|WP_Error Whether to treat the submission as spam or abort it.
 */
function aif_reject_jetpack_contact_form_without_turnstile(bool $is_spam): bool|WP_Error
{
    $turnstile_error = verify_turnstile();

    if ($turnstile_error === null) {
        return $is_spam;
    }

    return new WP_Error(
        'aif_turnstile_verification_failed',
        turnstile_friendly_error($turnstile_error)
    );
}

add_filter('jetpack_contact_form_is_spam', 'aif_reject_jetpack_contact_form_without_turnstile', 1, 1);

add_filter('wp_mail', function ($args) {

    $contact_page = get_page_by_path('contact');

    if (! $contact_page) {
        return $args;
    }

    $contact_path = parse_url(get_permalink($contact_page->ID), PHP_URL_PATH);
    $referer_url = wp_get_referer();

    if (! $referer_url) {
        return $args;
    }

    $referer_path = parse_url($referer_url, PHP_URL_PATH);

    if ($referer_path !== $contact_path) {
        return $args;
    }

    $form_id = isset($_POST['contact-form-id']) ? intval($_POST['contact-form-id']) : 0;

    if (!$form_id) {
        return $args;
    }

    $field_name = 'g' . $form_id;
    $selected = isset($_POST[$field_name]) ? trim($_POST[$field_name]) : '';

    if (!$selected) {
        return $args;
    }

    $map = [
        'Vos dons, votre adhésion, votre abonnement à la Chronique' => 'smd@amnesty.fr',
        'Un problème de connexion' => 'smd@amnesty.fr',
        "L\'engagement militant pour agir avec nous" => 'mobilisation@amnesty.fr',
    ];

    if (array_key_exists($selected, $map)) {
        $args['to'] = $map[$selected];
        error_log('📬 Mail Jetpack redirigé vers : ' . $map[$selected]);
    } else {
        error_log('⚠️ Valeur du select non reconnue : ' . $selected);
    }

    return $args;
});
