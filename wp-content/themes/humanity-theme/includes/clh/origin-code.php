<?php

declare(strict_types=1);

/**
 * External origin code of the CLH tunnel (AIF-450).
 *
 * A visitor coming from an emailing or a social post carries an origin code in the URL
 * (`reserved_originecode`, or its alias `code_origine`). The first signature sends it to
 * Salesforce, but the tunnel pages that follow do not carry it. The code is therefore kept
 * in the PHP session and used by the next tunnel signatures instead of the default code of
 * each petition. It is forgotten after the same inactivity window as the tunnel progress.
 *
 * The callers start the session; AMNESTY_CLH_TUNNEL_STATE_TTL is defined by petitions.php.
 */

const AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY = 'clh_origin_code';
const AMNESTY_ORIGIN_CODE_URL_PARAMS = ['reserved_originecode', 'code_origine'];

/**
 * The origin code if it is well formed (letters, digits, `_` or `-`, 50 characters at most).
 */
function amnesty_sanitize_origin_code(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);

    return preg_match('/^[A-Za-z0-9_-]{1,50}$/', $value) === 1 ? $value : null;
}

/**
 * The first well-formed origin code among the URL parameters, `reserved_originecode` first.
 *
 * @param array<string, mixed> $query_params
 */
function amnesty_get_origin_code_from_query(array $query_params): ?string
{
    foreach (AMNESTY_ORIGIN_CODE_URL_PARAMS as $name) {
        $code = amnesty_sanitize_origin_code($query_params[$name] ?? null);
        if ($code !== null) {
            return $code;
        }
    }

    return null;
}

/**
 * Keeps the code for the next tunnel signatures. A new code replaces the previous one.
 */
function amnesty_keep_clh_origin_code(string $code): void
{
    $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY] = ['code' => $code, 'at' => time()];
}

/**
 * The kept code, or null when there is none or when it was last used longer ago than the
 * tunnel inactivity window (it is then forgotten).
 */
function amnesty_get_kept_clh_origin_code(): ?string
{
    $kept = $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY] ?? null;
    if (!is_array($kept)) {
        return null;
    }

    $code = amnesty_sanitize_origin_code($kept['code'] ?? null);
    $kept_at = $kept['at'] ?? null;

    if ($code === null || !is_int($kept_at) || time() - $kept_at >= AMNESTY_CLH_TUNNEL_STATE_TTL) {
        unset($_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY]);

        return null;
    }

    return $code;
}

/**
 * Keeps the kept code for another inactivity window, like the tunnel progress after a
 * signature or a skip. Returns the kept code, or null when there is none.
 */
function amnesty_extend_kept_clh_origin_code(): ?string
{
    $kept = amnesty_get_kept_clh_origin_code();
    if ($kept !== null) {
        amnesty_keep_clh_origin_code($kept);
    }

    return $kept;
}

/**
 * Origin code sent to Salesforce for a petition signature.
 *
 * - A posted code is used as before; on a CLH petition, a well-formed one is also kept.
 * - Without a posted code, a tunnel signature uses the kept code, which stays kept for
 *   another inactivity window.
 * - Otherwise, the default code of the petition.
 */
function amnesty_resolve_petition_origin_code(mixed $posted, mixed $default, bool $is_clh_petition, bool $is_tunnel_submission): string
{
    // Same emptiness rule as before AIF-450 (`!empty($_POST['code_origine'])`).
    if (is_string($posted) && !empty($posted)) {
        $code = amnesty_sanitize_origin_code($posted);
        if ($is_clh_petition && $code !== null) {
            amnesty_keep_clh_origin_code($code);
        }

        return $posted;
    }

    if ($is_clh_petition && $is_tunnel_submission) {
        $kept = amnesty_extend_kept_clh_origin_code();
        if ($kept !== null) {
            return $kept;
        }
    }

    return is_scalar($default) ? (string) $default : '';
}
