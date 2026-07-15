<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require dirname(__DIR__, 2) . '/wp-content/plugins/aif-donor-space/includes/domain/bank/SEPA-mandate.php';

final class SepaMandateTest extends TestCase
{
    private static function mandate(string $status): object
    {
        return (object) ['Statut__c' => $status];
    }

    public function testReturnsNullWhenThereAreNoMandates(): void
    {
        self::assertNull(get_active_sepa_mandate([]));
    }

    public function testReturnsNullWhenNoMandateIsActive(): void
    {
        $mandates = [self::mandate('Résilié'), self::mandate('Suspendu')];

        self::assertNull(get_active_sepa_mandate($mandates));
    }

    public function testReturnsTheSingleActiveMandate(): void
    {
        $active = self::mandate('Actif');
        $mandates = [self::mandate('Résilié'), $active];

        self::assertSame($active, get_active_sepa_mandate($mandates));
    }

    public function testReturnsTheLastActiveMandateWhenSeveralAreActive(): void
    {
        // Documents current behaviour: the function does not stop at the first
        // active match, it keeps overwriting - so with several active mandates,
        // the *last* one in the array wins, not necessarily the most relevant one.
        $firstActive = self::mandate('Actif');
        $lastActive = self::mandate('Actif');
        $mandates = [$firstActive, self::mandate('Résilié'), $lastActive];

        self::assertSame($lastActive, get_active_sepa_mandate($mandates));
    }
}
