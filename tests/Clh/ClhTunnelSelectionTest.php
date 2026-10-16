<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/tunnel-selection.php';

final class ClhTunnelSelectionTest extends TestCase
{
    public function testRequestedPetitionIsSelectedAmongEligiblePetitions(): void
    {
        $eligible = [2 => ['id' => 17], 9 => ['id' => 42]];
        self::assertSame(['id' => 42], amnesty_select_clh_tunnel_petition($eligible, '42'));
        self::assertSame(['id' => 17], amnesty_select_clh_tunnel_petition($eligible, 17));
    }

    #[DataProvider('unavailableRequests')]
    public function testInvalidOrUnavailableRequestCannotBypassEligibility(mixed $requested): void
    {
        // The context excludes signed, skipped, expired and non-campaign petitions.
        $eligible = [5 => ['id' => 42]];
        self::assertSame(['id' => 42], amnesty_select_clh_tunnel_petition($eligible, $requested));
    }

    public static function unavailableRequests(): iterable
    {
        yield 'not in eligible list' => ['17'];
        yield 'missing' => [null];
        yield 'array query parameter' => [['42']];
        yield 'partially numeric' => ['42abc'];
        yield 'negative' => ['-42'];
        yield 'zero' => ['0'];
        yield 'too large' => [str_repeat('9', 100)];
    }

    public function testNoEligiblePetitionMeansEndOfTunnel(): void
    {
        self::assertNull(amnesty_select_clh_tunnel_petition([], '42'));
    }

    public function testFollowingSelectionStaysAmongRemainingPetitions(): void
    {
        $remaining = [3 => ['id' => 17], 6 => ['id' => 23]];
        self::assertContains(amnesty_select_clh_tunnel_petition($remaining), $remaining);
        self::assertContains(amnesty_select_clh_tunnel_petition($remaining, '42'), $remaining);
    }
}
