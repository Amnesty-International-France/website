<?php

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
