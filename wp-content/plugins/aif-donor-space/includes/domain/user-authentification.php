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

/**
 * Whether the password typed at the donor space login is the account's.
 *
 * Until October 2026, sign-up stored, and login checked, the password as
 * returned by sanitize_text_field() (tags, percent-encoded octets and repeated
 * whitespace removed). Since the login checks the password as typed (November
 * 2026), those accounts were refused whenever the two forms differ (MAINT-418).
 * Their legacy form is still accepted, then the account is rehashed with the
 * password as typed, so the fallback is used only once. A wrong password
 * changes nothing.
 *
 * Like wp_hash_password() and wp_authenticate(), the password is trimmed first:
 * otherwise a right password with a trailing space would fail the first check
 * and rehash the account on every login.
 */
function aif_check_login_password(WP_User $user, string $password): bool
{
    $password = trim($password);
    if (wp_check_password($password, $user->user_pass, $user->ID)) {
        return true;
    }

    $legacy_password = sanitize_text_field($password);
    if ('' === $legacy_password || $legacy_password === $password || !wp_check_password($legacy_password, $user->user_pass, $user->ID)) {
        return false;
    }

    wp_set_password($password, $user->ID);

    return true;
}
