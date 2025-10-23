<?php

add_filter('wp_mail', function ($args) {

    if (!is_page('contact')) {
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
        "L'engagement avec Amnesty" => 'mobilisation@amnesty.fr',
        'La Chronique' => 'chronique@amnesty.fr',
    ];

    if (array_key_exists($selected, $map)) {
        $args['to'] = $map[$selected];
        error_log('📬 Mail Jetpack redirigé vers : ' . $map[$selected]);
    } else {
        error_log('⚠️ Valeur du select non reconnue : ' . $selected);
    }

    return $args;
});
