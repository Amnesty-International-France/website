<?php

/**
 * Cloudflare Turnstile
 */

/**
 * Whether Turnstile protects a form. TURNSTILE_ENABLED=0 switches it off on the
 * switchable forms only, listed in README.md; the other forms keep Turnstile
 * whatever the value.
 *
 * Only `0` switches it off; an unset, empty or other value keeps it on.
 *
 * @param bool $switchable whether the form follows TURNSTILE_ENABLED
 */
function aif_turnstile_enabled(bool $switchable): bool
{
    return !$switchable || '0' !== getenv('TURNSTILE_ENABLED');
}

/**
 * The site key, or an empty string when Turnstile is off for the form. A form must
 * then render no widget: the Turnstile script still runs for the other forms of
 * the page, and would block the submission of a form holding an empty widget.
 *
 * @param bool $switchable whether the form follows TURNSTILE_ENABLED
 */
function aif_turnstile_site_key(bool $switchable = false): string
{
    if (!aif_turnstile_enabled($switchable)) {
        return '';
    }

    return (string) getenv('TURNSTILE_SITE_KEY');
}

function aif_turnstile_secret_key(): string
{
    return (string) getenv('TURNSTILE_SECRET_KEY');
}

add_action('wp_enqueue_scripts', function () {
    if (!aif_turnstile_site_key()) {
        return;
    }

    $theme = wp_get_theme();

    wp_enqueue_script(
        'aif-turnstile',
        get_template_directory_uri() . '/assets/scripts/turnstile.js',
        [],
        $theme->get('Version'),
        ['in_footer' => false]
    );
    wp_enqueue_script(
        'cf-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        ['aif-turnstile'],
        null,
        [
            'in_footer' => false,
            'strategy'  => 'defer',
        ]
    );
});

add_filter('wp_resource_hints', function (array $urls, string $relation_type): array {
    if ('preconnect' !== $relation_type || !aif_turnstile_site_key()) {
        return $urls;
    }

    if (!in_array('https://challenges.cloudflare.com', $urls, true)) {
        $urls[] = 'https://challenges.cloudflare.com';
    }

    return $urls;
}, 10, 2);

/**
 * @param bool $switchable whether the form follows TURNSTILE_ENABLED
 *
 * @return string|null the error code, or null when the submission is accepted
 */
function verify_turnstile(bool $switchable = false): ?string
{
    if (!aif_turnstile_enabled($switchable)) {
        return null;
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sent as is to Cloudflare siteverify, never output or stored; Cloudflare documents no token charset.
    $response = $_POST['cf-turnstile-response'] ?? '';
    if (empty($response)) {
        return 'missing-input-response';
    }

    $result = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'body' => [
            'secret' => aif_turnstile_secret_key(),
            'response' => $response,
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- set by the server, sent as is to Cloudflare siteverify.
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ],
    ]);

    if (is_wp_error($result)) {
        return 'wp-http-error: ' . $result->get_error_message();
    }

    $body = json_decode(wp_remote_retrieve_body($result), true);

    if (!empty($body['success'])) {
        return null;
    }

    $codes = $body['error-codes'] ?? ['unknown-error'];
    return $codes[0];
}

function turnstile_friendly_error(string $error): string
{
    return match ($error) {
        'missing-input-response' => 'La vérification de sécurité n’a pas pu être effectuée. Veuillez recharger la page puis réessayer.',
        'invalid-input-response' => 'La vérification de sécurité a échoué. Veuillez recharger la page puis réessayer.',
        'timeout-or-duplicate' => 'La session de vérification a expiré. Veuillez recharger la page puis réessayer.',
        'bad-request' => 'La requête de vérification est invalide. Veuillez réessayer.',
        'internal-error' => 'Le service de vérification est temporairement indisponible. Veuillez réessayer dans quelques instants.',
        'missing-input-secret', 'invalid-input-secret', 'invalid-widget-id', 'invalid-parsed-secret' => 'Une erreur de configuration est survenue. Veuillez contacter le support.',
        default => 'La vérification de sécurité a échoué. Veuillez réessayer.',
    };
}
