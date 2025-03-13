<?php

function aif_generate_random_hash()
{
    return bin2hex(random_bytes(12));
}


function send_reset_password_email($to_email, $url)
{
    $api_key = getenv('AIF_MAILGUN_TOKEN');
    $url = getenv('AIF_MAILGUN_URL') . '/'. getenv('AIF_MAILGUN_DOMAIN') . '/messages';

    $variables = [
        "url" => $url
    ];

    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => array(
            'from' => 'noreply@' .  getenv('AIF_MAILGUN_DOMAIN'),
            'to' => $to_email,
            'subject' => 'Amnesty France- réinitialiser votre mot de passe',
            't:variables' => json_encode($variables),
            'template' => 'espace-don réinitialisation du mot de passe'

        ),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode('api:' . $api_key)
        )
    ));

    if (is_wp_error($response)) {
        return false;
    } else {
        return true;
    }
}
