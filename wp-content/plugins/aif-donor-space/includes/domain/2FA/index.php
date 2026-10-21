<?php

const AIF_2FA_CODE_TTL = 1800;

function generate_2fa_code()
{
    return random_int(100000, 999999);
}

function store_2fa_code($user_id, $code)
{
    $user = get_userdata($user_id);

    if (!$user) {
        delete_2fa_code($user_id);

        return false;
    }

    $email = $user->user_email;
    update_user_meta($user_id, '2fa_code', $code);
    update_user_meta($user_id, '2fa_code_expires', time() + AIF_2FA_CODE_TTL);
    update_user_meta($user_id, '2fa_code_email', $email);

    return $email;
}

function delete_2fa_code($user_id)
{
    delete_user_meta($user_id, '2fa_code');
    delete_user_meta($user_id, '2fa_code_expires');
    delete_user_meta($user_id, '2fa_code_email');
}

/**
 * Returns 'valid', 'invalid' or 'blocked'. The block is checked before the
 * code, so a blocked account rejects even the right code. A code is single-use
 * and expires after AIF_2FA_CODE_TTL seconds.
 */
function check_2fa_code($user_id, $code)
{
    if ((int) get_login_blocked_until($user_id) > time()) {
        return 'blocked';
    }

    $stored_code = (string) get_2fa_code($user_id);
    $expires = (int) get_user_meta($user_id, '2fa_code_expires', true);
    $sent_to = get_user_meta($user_id, '2fa_code_email', true);
    $user = get_userdata($user_id);

    if ($stored_code !== '' && is_string($code) && $expires >= time()
        && is_string($sent_to) && $sent_to !== '' && $user
        && 0 === strcasecmp($sent_to, $user->user_email)
        && hash_equals($stored_code, $code)) {
        delete_2fa_code($user_id);
        reset_login_attempts($user_id);

        return 'valid';
    }

    return limit_login_attempts($user_id) ? 'invalid' : 'blocked';
}

function store_email_is_verified($user_id, $verified_email)
{
    update_user_meta($user_id, 'email-verfied', strtolower($verified_email));
}

function get_email_is_verified($user_id)
{
    $verified_email = get_user_meta($user_id, 'email-verfied', true);
    $user = get_userdata($user_id);

    // Older boolean flags do not identify the address that was verified.
    return is_string($verified_email) && $verified_email !== '' && $user
        && 0 === strcasecmp($verified_email, $user->user_email);
}

function aif_reset_email_verification_on_email_change($user_id, $old_user_data)
{
    $user = get_userdata($user_id);

    if (!$user || 0 === strcasecmp($old_user_data->user_email, $user->user_email)) {
        return;
    }

    delete_user_meta($user_id, 'email-verfied');
    delete_2fa_code($user_id);
    delete_email_token($user_id);

    if (in_array('subscriber', (array) $user->roles, true)) {
        WP_Session_Tokens::get_instance($user_id)->destroy_all();
    }
}

add_action('profile_update', 'aif_reset_email_verification_on_email_change', 10, 2);


function get_2fa_code($user_id)
{
    return get_user_meta($user_id, '2fa_code', true);
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
