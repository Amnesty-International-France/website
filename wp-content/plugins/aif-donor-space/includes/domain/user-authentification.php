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
 * Rehashes the account with the password as typed when only its legacy form
 * matches, so that wp_signon(), which checks the password as typed, accepts it.
 *
 * Until October 2026, sign-up stored, and login checked, the password as
 * returned by sanitize_text_field() (trimmed, whitespace collapsed, tags and
 * percent-encoded octets removed). Since the login checks the password as
 * typed (November 2026), those accounts were refused whenever the two forms
 * differ (MAINT-418). A wrong password changes nothing.
 */
function aif_upgrade_legacy_login_password(WP_User $user, string $password): void
{
    if (wp_check_password($password, $user->user_pass, $user->ID)) {
        return;
    }

    $legacy_password = sanitize_text_field($password);
    if ('' === $legacy_password || $legacy_password === $password || !wp_check_password($legacy_password, $user->user_pass, $user->ID)) {
        return;
    }

    wp_set_password($password, $user->ID);
}
