<?php

function aif_generate_random_hash()
{
    return bin2hex(random_bytes(12));
}


function send_reset_password_email($to_email, $authentification_url)
{
    $api_key = getenv('AIF_MAILGUN_TOKEN');
    $url = getenv('AIF_MAILGUN_URL') . '/'. getenv('AIF_MAILGUN_DOMAIN') . '/messages';

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
            'template' => 'espace-don réinitialisation du mot de passe',
        ],
        'timeout'   => 30,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode('api:' . $api_key),
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    } else {
        return true;
    }
}

if (!defined('AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS')) {
    define('AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 10);
}

if (!defined('AIF_LOGIN_RATE_LIMIT_WINDOW')) {
    // Seconds.
    define('AIF_LOGIN_RATE_LIMIT_WINDOW', 15 * 60);
}

const AIF_LOGIN_RATE_LIMIT_MESSAGE = 'Trop de tentatives de connexion. Veuillez réessayer dans quelques minutes.';

/**
 * Counts a donor space login attempt for the client IP and tells whether it
 * may go on: at most AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS attempts per
 * AIF_LOGIN_RATE_LIMIT_WINDOW seconds, from the first attempt of the window.
 *
 * The IP is REMOTE_ADDR, as in verify_turnstile(): the site does not read
 * CF-Connecting-IP anywhere, so the web server must restore the visitor's IP
 * when it sits behind Cloudflare.
 */
function aif_register_login_attempt_from_ip()
{
    $key = 'aif_login_ip_' . wp_hash((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $now = time();
    $window = get_transient($key);

    if (!is_array($window) || !isset($window['start'], $window['count']) || $window['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW <= $now) {
        $window = ['start' => $now, 'count' => 0];
    }

    if ($window['count'] >= AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS) {
        return false;
    }

    $window['count']++;
    // Keep the window's end: rewriting the transient must not extend it.
    set_transient($key, $window, max(1, $window['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW - $now));

    return true;
}
