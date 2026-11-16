<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/petitions/signature-status.php';

/**
 * Each `signature_status` set by a rejected signature maps to the message shown to the
 * visitor; any other value maps to no message.
 */
final class SignatureStatusMessageTest extends TestCase
{
    #[DataProvider('statuses')]
    public function testStatusMapsToItsMessage(string $status, string $expected): void
    {
        self::assertSame($expected, amnesty_get_signature_status_message($status));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function statuses(): iterable
    {
        yield 'invalid' => ['invalid', 'Merci de renseigner une adresse email valide avant de signer.'];
        yield 'missing fields' => ['missing_fields', 'Merci de renseigner vos nom et prénom pour signer la pétition.'];
        yield 'expired' => [
            'expired',
            'La pétition que vous avez essayé de signer est terminée, merci pour votre soutien.',
        ];
        yield 'error' => ['error', 'Une erreur est survenue pendant la signature. Veuillez réessayer.'];
        yield 'turnstile' => ['turnstile', 'La vérification de sécurité a échoué. Veuillez réessayer.'];
        yield 'unknown' => ['unknown', ''];
        yield 'empty' => ['', ''];
    }
}
