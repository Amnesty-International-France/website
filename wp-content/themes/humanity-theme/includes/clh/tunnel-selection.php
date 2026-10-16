<?php

declare(strict_types=1);

/**
 * Select only among the campaign petitions still eligible for signature.
 * An unavailable or invalid requested ID preserves the usual random selection.
 * The request parameter is not retained by the sign/skip redirects, so following
 * petitions continue to be chosen at random.
 *
 * @param array<int, array<string, mixed>> $eligible_petitions Active, unsigned, unskipped petitions.
 * @param mixed $requested_id Optional clh_petition query parameter.
 *
 * @return array<string, mixed>|null
 */
function amnesty_select_clh_tunnel_petition(array $eligible_petitions, mixed $requested_id = null): ?array
{
    if (!$eligible_petitions) {
        return null;
    }

    if ((is_int($requested_id) || is_string($requested_id)) && ctype_digit((string) $requested_id)) {
        foreach ($eligible_petitions as $petition) {
            if ((string) $petition['id'] === (string) $requested_id) {
                return $petition;
            }
        }
    }

    return $eligible_petitions[array_rand($eligible_petitions)];
}
