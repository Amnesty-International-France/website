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

if (!defined('AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS')) {
    define('AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 10);
}

if (!defined('AIF_LOGIN_RATE_LIMIT_WINDOW')) {
    // Seconds.
    define('AIF_LOGIN_RATE_LIMIT_WINDOW', 15 * 60);
}

const AIF_LOGIN_RATE_LIMIT_MESSAGE = 'Trop de tentatives de connexion. Veuillez réessayer dans quelques minutes.';

/**
 * Transient key of the client for the donor space login limit: at most
 * AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS failed logins per
 * AIF_LOGIN_RATE_LIMIT_WINDOW seconds, from the first failure of the window.
 *
 * The IP is REMOTE_ADDR, as in verify_turnstile(). Behind Cloudflare, the
 * cloudflare plugin sets it to CF-Connecting-IP on plugins_loaded.
 */
function aif_login_rate_limit_key()
{
    $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
    $packed = inet_pton($ip);
    // One IPv6 /64 is one home, office or phone, whose devices pick any
    // address in it: count the whole block as one client.
    if ($packed !== false && strlen($packed) === 16) {
        $ip = inet_ntop(substr($packed, 0, 8) . str_repeat("\0", 8));
    }

    return 'aif_login_ip_' . wp_hash($ip);
}

/**
 * Current window of the client: when it started and how many logins failed
 * in it.
 *
 * @return array{start: int, count: int}
 */
function aif_login_rate_limit_window()
{
    $window = get_transient(aif_login_rate_limit_key());

    if (!is_array($window) || !isset($window['start'], $window['count']) || $window['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW <= time()) {
        return ['start' => time(), 'count' => 0];
    }

    return $window;
}

/** Seconds until the client may try again, or 0 if it is not blocked. */
function aif_login_blocked_for_seconds()
{
    $window = aif_login_rate_limit_window();

    if ($window['count'] < AIF_LOGIN_RATE_LIMIT_MAX_ATTEMPTS) {
        return 0;
    }

    return max(1, $window['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW - time());
}

function aif_register_failed_login_from_ip()
{
    $window = aif_login_rate_limit_window();
    $window['count']++;
    // Keep the window's end: rewriting the transient must not extend it.
    set_transient(aif_login_rate_limit_key(), $window, max(1, $window['start'] + AIF_LOGIN_RATE_LIMIT_WINDOW - time()));
}
