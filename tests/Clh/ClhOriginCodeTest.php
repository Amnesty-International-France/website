<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

if (!defined('AMNESTY_CLH_TUNNEL_STATE_TTL')) {
    define('AMNESTY_CLH_TUNNEL_STATE_TTL', 15 * 60);
}

require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/origin-code.php';

/**
 * AIF-450: the origin code of the incoming link is kept for the signatures of the CLH tunnel.
 */
final class ClhOriginCodeTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testCodePostedOnACampaignPetitionIsUsedByTheNextTunnelSignatures(): void
    {
        self::assertSame('26BJPE1786', amnesty_resolve_petition_origin_code('26BJPE1786', 'DEFAULT1', true, false));
        self::assertSame('26BJPE1786', amnesty_resolve_petition_origin_code(null, 'DEFAULT2', true, true));
        self::assertSame('26BJPE1786', amnesty_resolve_petition_origin_code('', 'DEFAULT3', true, true));
    }

    public function testNewCodeReplacesTheKeptOne(): void
    {
        amnesty_keep_clh_origin_code('CODEA');

        self::assertSame('CODEB', amnesty_resolve_petition_origin_code('CODEB', 'DEFAULT', true, true));
        self::assertSame('CODEB', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, true));
    }

    public function testMalformedPostedCodeIsSentAsBeforeButNotKept(): void
    {
        self::assertSame('<b>x</b>', amnesty_resolve_petition_origin_code('<b>x</b>', 'DEFAULT', true, false));
        self::assertSame('DEFAULT', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, true));
    }

    public function testCodePostedOutsideTheCampaignIsNotKept(): void
    {
        self::assertSame('CODEA', amnesty_resolve_petition_origin_code('CODEA', 'DEFAULT', false, false));
        self::assertArrayNotHasKey(AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY, $_SESSION);
    }

    public function testKeptCodeIsOnlyUsedByTunnelSignatures(): void
    {
        amnesty_keep_clh_origin_code('CODEA');

        self::assertSame('DEFAULT', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, false));
        self::assertSame('DEFAULT', amnesty_resolve_petition_origin_code(null, 'DEFAULT', false, false));
    }

    public function testKeptCodeIsForgottenAfterTheTunnelInactivityWindow(): void
    {
        $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY] = ['code' => 'CODEA', 'at' => time() - AMNESTY_CLH_TUNNEL_STATE_TTL];

        self::assertSame('DEFAULT', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, true));
        self::assertArrayNotHasKey(AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY, $_SESSION);
    }

    public function testUsingTheKeptCodeKeepsItForAnotherWindow(): void
    {
        $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY] = ['code' => 'CODEA', 'at' => time() - AMNESTY_CLH_TUNNEL_STATE_TTL + 60];

        self::assertSame('CODEA', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, true));
        self::assertGreaterThanOrEqual(time() - 1, $_SESSION[AMNESTY_CLH_ORIGIN_CODE_SESSION_KEY]['at']);
    }

    public function testWithoutAnyCodeThePetitionDefaultIsUsed(): void
    {
        self::assertSame('DEFAULT', amnesty_resolve_petition_origin_code(null, 'DEFAULT', true, true));
        self::assertSame('', amnesty_resolve_petition_origin_code(null, null, true, true));
        self::assertSame('', amnesty_resolve_petition_origin_code(['CODEA'], '', false, false));
    }

    #[DataProvider('queries')]
    public function testOriginCodeIsReadFromTheUrl(array $query, ?string $expected): void
    {
        self::assertSame($expected, amnesty_get_origin_code_from_query($query));
    }

    public static function queries(): iterable
    {
        yield 'reserved_originecode' => [['reserved_originecode' => '26BJPE1786', 'utm_source' => 'email_action'], '26BJPE1786'];
        yield 'code_origine alias' => [['code_origine' => 'CODEA'], 'CODEA'];
        yield 'reserved_originecode first' => [['code_origine' => 'CODEA', 'reserved_originecode' => 'CODEB'], 'CODEB'];
        yield 'malformed falls back to the alias' => [['reserved_originecode' => 'a b', 'code_origine' => 'CODEA'], 'CODEA'];
        yield 'trimmed' => [['code_origine' => ' CODEA '], 'CODEA'];
        yield 'too long' => [['code_origine' => str_repeat('A', 51)], null];
        yield 'array' => [['code_origine' => ['CODEA']], null];
        yield 'none' => [['utm_source' => 'email_action'], null];
    }
}
