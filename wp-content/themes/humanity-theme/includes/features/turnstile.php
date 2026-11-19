<?php

/**
 * Cloudflare Turnstile
 */

/**
 * Whether Turnstile is on. Only TURNSTILE_ENABLED=0 switches it off; an unset,
 * empty or other value keeps it on.
 *
 * When it is off the keys stay configured, but no site key is exposed: no
 * Turnstile script is loaded, no widget is rendered, and verify_turnstile()
 * accepts every submission.
 */
function aif_turnstile_enabled(): bool
{
    return '0' !== getenv('TURNSTILE_ENABLED');
}

function aif_turnstile_site_key(): string
{
    if (!aif_turnstile_enabled()) {
        return '';
    }

    return (string) getenv('TURNSTILE_SITE_KEY');
}

function aif_turnstile_secret_key(): string
{
    return (string) getenv('TURNSTILE_SECRET_KEY');
}

/**
 * Markup of a Turnstile widget for a protected form, or '' when Turnstile is off.
 *
 * Declared as auto-escaped in phpcs.xml: the site key is its only variable part.
 *
 * @param bool $interaction_only Show the widget only when Cloudflare needs an interaction.
 */
function aif_turnstile_widget(bool $interaction_only = false): string
{
    $site_key = aif_turnstile_site_key();

    if ('' === $site_key) {
        return '';
    }

    return sprintf(
        '<div class="cf-turnstile" data-callback="aifTurnstileSuccess" data-error-callback="aifTurnstileFailure"%s'
        . ' data-expired-callback="aifTurnstileFailure" data-timeout-callback="aifTurnstileFailure"'
        . ' data-unsupported-callback="aifTurnstileFailure" data-sitekey="%s"></div>',
        $interaction_only ? ' data-appearance="interaction-only"' : '',
        esc_attr($site_key)
    );
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

function verify_turnstile(): ?string
{
    if (!aif_turnstile_enabled()) {
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
