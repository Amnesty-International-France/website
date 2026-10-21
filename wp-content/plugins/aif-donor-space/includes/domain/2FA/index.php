<?php

require_once dirname(__DIR__) . '/expiring-secret.php';

const AIF_2FA_CODE_TTL = 900;

function generate_2fa_code()
{
    return (string) random_int(100000, 999999);
}

function store_2fa_code($user_id, $code)
{
    aif_store_expiring_secret($user_id, '2fa_code', $code, AIF_2FA_CODE_TTL);
}

function is_2fa_code_valid($user_id, $code)
{
    return aif_is_expiring_secret_valid($user_id, '2fa_code', $code);
}

function delete_2fa_code($user_id)
{
    aif_delete_expiring_secret($user_id, '2fa_code');
}

function is_login_blocked($user_id)
{
    return (int) get_login_blocked_until($user_id) > time();
}

/**
 * Every submission counts as an attempt and the lockout is checked before
 * the code, so at most 10 codes are compared per lockout window and a
 * correct guess made while blocked is refused. Completing verification
 * resets the counter.
 */
function aif_check_2fa_code($user_id, $code)
{
    if (is_login_blocked($user_id) || !limit_login_attempts($user_id)) {
        return 'blocked';
    }

    return is_2fa_code_valid($user_id, $code) ? 'valid' : 'invalid';
}

function store_email_is_verified($user_id)
{
    $user = get_userdata($user_id);

    if ($user) {
        update_user_meta($user_id, 'email-verfied', strtolower($user->user_email));
    }
}

function get_email_is_verified($user_id)
{
    $verified_email = get_user_meta($user_id, 'email-verfied', true);
    $user = get_userdata($user_id);

    // A legacy boolean flag does not identify the address that was verified.
    return is_string($verified_email) && $verified_email !== '' && $user
        && 0 === strcasecmp($verified_email, $user->user_email);
}


function send_2fa_code($to_email, $code)
{
    $api_key = getenv('AIF_MAILGUN_TOKEN');
    $url = getenv('AIF_MAILGUN_URL') . '/'. getenv('AIF_MAILGUN_DOMAIN') . '/messages';

    $variables = [
        'code' => $code,
    ];

    $response = wp_remote_post($url, [
        'method' => 'POST',
        'body' => [
            'from' => 'noreply@' .  getenv('AIF_MAILGUN_DOMAIN'),
            'to' => $to_email,
            'subject' => 'Amnesty France- Vérifier votre email',
            't:variables' => json_encode($variables),
            'template' => '2facode',
        ],
        'timeout'   => 30,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode('api:' . $api_key),
        ]]);

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
