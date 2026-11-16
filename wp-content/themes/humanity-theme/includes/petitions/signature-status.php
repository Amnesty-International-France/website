<?php

declare(strict_types=1);

/**
 * Message of a signature rejected for its email, shown by the tunnel and the petition page
 * after the server check, and by the browser before the submit (`data-invalid-message` on
 * the email input).
 */
const AMNESTY_PETITION_INVALID_EMAIL_MESSAGE = 'Merci de renseigner une adresse email valide avant de signer.';

/**
 * Message shown to the visitor for the `signature_status` of a rejected signature,
 * on the CLH tunnel and on the petition page; `''` for any other status.
 */
function amnesty_get_signature_status_message(string $status): string
{
    return match ($status) {
        'invalid' => AMNESTY_PETITION_INVALID_EMAIL_MESSAGE,
        'missing_fields' => 'Merci de renseigner vos nom et prénom pour signer la pétition.',
        'expired' => 'La pétition que vous avez essayé de signer est terminée, merci pour votre soutien.',
        'error' => 'Une erreur est survenue pendant la signature. Veuillez réessayer.',
        'turnstile' => 'La vérification de sécurité a échoué. Veuillez réessayer.',
        default => '',
    };
}

/**
 * Message for the `signature_status` of the current request; `''` when it has none.
 */
function amnesty_get_requested_signature_status_message(): string
{
    return amnesty_get_signature_status_message(sanitize_key($_GET['signature_status'] ?? ''));
}
