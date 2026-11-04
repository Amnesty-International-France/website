<?php

declare(strict_types=1);

/**
 * Session key holding the fields of a tunnel signature rejected by the server.
 */
const AMNESTY_CLH_SIGNATURE_FORM_SESSION_KEY = 'clh_signature_form';

/**
 * Fields of the anonymous tunnel form shown again after a rejected signature, with the
 * length of their column in the users table (includes/petitions/tables.php). Values are
 * kept before the Turnstile check, so their size is bounded.
 */
const AMNESTY_CLH_SIGNATURE_FORM_FIELDS = [
    'user_email' => 255,
    'civility' => 20,
    'user_firstname' => 100,
    'user_lastname' => 100,
    'user_zipcode' => 20,
    'user_country' => 100,
    'user_phone' => 50,
];

/**
 * Tunnel URL that shows a signature error above the petition the visitor tried to sign.
 * The tunnel selector falls back to a random petition when that one is no longer eligible.
 */
function amnesty_get_clh_tunnel_retry_url(int $petition_id, string $status): string
{
    return add_query_arg(
        ['signature_status' => $status, 'clh_petition' => $petition_id],
        amnesty_get_clh_petition_tunnel_url()
    );
}

/**
 * Keeps the fields of a rejected tunnel signature for the next tunnel display.
 * The email goes through sanitize_text_field(), not sanitize_email(): the latter empties
 * the malformed address the visitor has to correct. Expects an active session.
 *
 * @param array<string, mixed> $posted Submitted form values, slashed by WordPress.
 */
function amnesty_remember_clh_signature_form(array $posted): void
{
    $values = [];

    foreach (AMNESTY_CLH_SIGNATURE_FORM_FIELDS as $field => $max_length) {
        $value = $posted[$field] ?? '';
        $values[$field] = is_string($value) ? mb_substr(sanitize_text_field(wp_unslash($value)), 0, $max_length) : '';
    }

    $_SESSION[AMNESTY_CLH_SIGNATURE_FORM_SESSION_KEY] = $values;
}

/**
 * Returns the fields kept by amnesty_remember_clh_signature_form() and forgets them,
 * so they are shown once. An unexpected payload is ignored.
 *
 * @return array<string, string>
 */
function amnesty_pull_clh_signature_form(): array
{
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized when stored by amnesty_remember_clh_signature_form().
    $values = $_SESSION[AMNESTY_CLH_SIGNATURE_FORM_SESSION_KEY] ?? null;
    unset($_SESSION[AMNESTY_CLH_SIGNATURE_FORM_SESSION_KEY]);

    if (!is_array($values) || array_filter($values, static fn ($value) => !is_string($value)) !== []) {
        return [];
    }

    return $values;
}
