<?php

function generate_2fa_code()
{
    return rand(100000, 999999);
}

function store_2fa_code($user_id, $code)
{
    update_user_meta($user_id, '2fa_code', $code);
}

function store_email_is_verified($user_id)
{
    update_user_meta($user_id, 'email-verfied', true);
}

function get_email_is_verified($user_id)
{
    return get_user_meta($user_id, 'email-verfied');
}


function get_2fa_code($user_id)
{
    return get_user_meta($user_id, '2fa_code', true);
}


function send_2fa_code($to_email, $code, $verification_url)
{
    $api_key = MAILGUN_TOKEN;
    $url = MAILGUN_URL . '/'. MAILGUN_DOMAIN . '/messages';

    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => array(
            'from' => 'noreply@' . MAILGUN_DOMAIN,
            'to' => $to_email,
            'subject' => 'Amnesty France- Vérifier votre email',
            'text' => 'Le code de vérification est ' . $code . '. Cliquer ici pour activer votre compte: ' . $verification_url
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
