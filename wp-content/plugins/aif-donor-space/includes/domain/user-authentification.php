<?php

require_once __DIR__ . '/mailgun.php';

function aif_generate_random_hash()
{
    return bin2hex(random_bytes(12));
}


function send_reset_password_email($to_email, $authentification_url)
{
    $api_key = getenv('AIF_MAILGUN_TOKEN');
    $url = getenv('AIF_MAILGUN_URL') . '/'. getenv('AIF_MAILGUN_DOMAIN') . '/messages';

    $template = 'espace-don réinitialisation du mot de passe';
    $variables = [
        'url' => $authentification_url,
    ];

    $response = wp_remote_post($url, [
        'method' => 'POST',
        'body' => [
            'from' => 'noreply@' .  getenv('AIF_MAILGUN_DOMAIN'),
            'to' => $to_email,
            'subject' => 'Amnesty France- réinitialiser votre mot de passe',
            't:variables' => json_encode($variables),
            'template' => $template,
        ],
        'timeout'   => 30,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode('api:' . $api_key),
        ],
    ]);

    return aif_is_mailgun_send_successful($response, $template);
}
