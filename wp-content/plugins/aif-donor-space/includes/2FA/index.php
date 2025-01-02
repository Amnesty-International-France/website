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
    return get_user_meta($user_id, 'email-verfied', true);
}


function get_2fa_code($user_id)
{
    return get_user_meta($user_id, '2fa_code', true);
}


function send_2fa_code($to_email, $code, $verification_url)
{
    $api_key = getenv('AIF_MAILGUN_TOKEN');
    $url = getenv('AIF_MAILGUN_URL') . '/'. getenv('AIF_MAILGUN_DOMAIN') . '/messages';

    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => array(
            'from' => 'noreply@' .  getenv('AIF_MAILGUN_DOMAIN'),
            'to' => $to_email,
            'subject' => 'Amnesty France- Vérifier votre email',
            'text' => 'Le code de vérification est '. $code . 'Rendez-vous sur cette url vour activer votre compte: ' . $verification_url
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

function get_login_blocked_until($user_id)
{

    return get_user_meta($user_id, 'login_blocked_until', true);
}

function can_check_code($user_id)
{

    $blocked_until = get_login_blocked_until($user_id);
    return $blocked_until && $blocked_until <= time();

}

function limit_login_attempts($user_id)
{

    if (can_check_code($user_id)) {
        reset_login_attempts($user_id);
    }

    $attempts = (int)get_user_meta($user_id, 'login_attempts', true) ?? 0;


    if ($attempts >= 10) {
        update_user_meta($user_id, 'login_blocked_until', time() + 3600); // 1 heure
        return false;
    } else {
        update_user_meta($user_id, 'login_attempts', $attempts + 1);
        return true;
    }
}

function reset_login_attempts($user_id)
{
    update_user_meta($user_id, 'login_attempts', 0);
    delete_user_meta($user_id, 'login_blocked_until');
}
