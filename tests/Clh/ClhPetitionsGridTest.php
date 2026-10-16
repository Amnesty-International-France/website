<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhPetitionsGridTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
        require_once __DIR__ . '/template-stand-ins.php';

        function get_the_ID(): int
        {
            return 2026;
        }

        function amnesty_get_active_clh_campaign_for_page(int $page_id): ?object
        {
            return amnesty_is_clh_petition_tunnel_active() ? amnesty_get_clh_petition_campaign_page() : null;
        }

        function amnesty_is_petition_not_expired(int $id): bool
        {
            return !in_array($id, $GLOBALS['__phpunit_closed_petitions'] ?? [], true);
        }

        function wp_get_attachment_image(int $id, string $size, bool $icon, array $attributes): string
        {
            return '<img src="/photo-' . $id . '.jpg" class="petition-image" alt="">';
        }

        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => 2026];
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [(object) ['ID' => 1, 'post_title' => 'Titre complet']];
        $GLOBALS['__phpunit_clh_tunnel_url'] = '/campagne/';
        $GLOBALS['__phpunit_clh_tunnel_active'] = true;
        $GLOBALS['__phpunit_post_permalinks'] = [1 => '/petitions/1/'];
        $GLOBALS['__phpunit_acf_field_values'] = [2026 => [
            'end_date_highlight_clh' => '2027-01-10 23:59:59',
            'final_signatures_clh' => '0',
            'campaign_year_clh' => '2026',
        ]];
    }

    #[DataProvider('campaigns')]
    public function testHeadingUsesFinalYearOnlyForACompletedCampaign(bool $active, bool $complete_results, string $heading): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = $active;
        if (!$complete_results) {
            unset($GLOBALS['__phpunit_acf_field_values'][2026]['final_signatures_clh']);
        }

        $html = $this->render();
        self::assertStringContainsString($heading, $html);
    }

    public function testGridRendersAtMostTenCardsInHelperOrder(): void
    {
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [];
        for ($id = 1; $id <= 12; $id++) {
            $GLOBALS['__phpunit_clh_campaign_petitions'][] = (object) ['ID' => $id, 'post_title' => "Pétition $id"];
            $GLOBALS['__phpunit_acf_field_values'][$id]['date_de_fin'] = date('d.m.Y', time() + (20 - $id) * 86400);
        }
        $html = $this->render();
        preg_match_all('/data-petition-id="(\d+)"/', $html, $matches);
        self::assertSame(range(12, 3), array_map('intval', $matches[1]));
    }

    public function testGridIsAbsentWithoutPetitions(): void
    {
        $GLOBALS['__phpunit_clh_campaign_petitions'] = [];
        self::assertSame('', trim($this->render()));
    }

    public function testCardUsesPreparedValuesAndTargetedTunnelLink(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][1] = ['short_title' => 'Titre mobile', 'objectif_signatures' => '3000'];
        $GLOBALS['__phpunit_clh_signature_counts'][1] = 1999;
        $GLOBALS['__phpunit_post_thumbnail_ids'][1] = 42;
        $html = $this->render();
        self::assertStringContainsString('href="/campagne/?clh_petition=1"', $html);
        self::assertStringContainsString('Signer la pétition', $html);
        self::assertStringContainsString('clh-petition-title-desktop">Titre complet', $html);
        self::assertStringContainsString('clh-petition-title-mobile">Titre mobile', $html);
        self::assertStringContainsString('width: 66%', $html);
        self::assertStringContainsString('1 999 signatures', $html);
        self::assertStringContainsString('/photo-42.jpg', $html);
    }

    public function testMissingPhotoAndShortTitleKeepTheCardUsable(): void
    {
        $html = $this->render();
        self::assertStringContainsString('<div class="petition-thumbnail"></div>', $html);
        self::assertStringContainsString('clh-petition-title-mobile">Titre complet', $html);
        self::assertStringContainsString('Signer la pétition', $html);
    }

    public function testClosedPetitionRemainsConsultableDuringCampaign(): void
    {
        $GLOBALS['__phpunit_closed_petitions'] = [1];
        $html = $this->render();
        self::assertStringContainsString('Voir la pétition', $html);
        self::assertStringContainsString('href="/petitions/1/"', $html);
        self::assertStringNotContainsString('clh_petition=', $html);
    }

    public function testClosedCampaignUsesPetitionPagesEvenForActivePetitions(): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = false;
        $html = $this->render();
        self::assertStringContainsString('Voir la pétition', $html);
        self::assertStringContainsString('href="/petitions/1/"', $html);
        self::assertStringNotContainsString('clh_petition=', $html);
    }

    public function testCustomHeadingsAreStateSpecificAndEscaped(): void
    {
        $GLOBALS['__phpunit_acf_field_values'][2026]['petitions_heading_active_clh'] = 'En cours <script>';
        $GLOBALS['__phpunit_acf_field_values'][2026]['petitions_heading_closed_clh'] = 'Bilan personnalisé';
        $html = $this->render();
        self::assertStringContainsString('En cours &lt;script&gt;', $html);
        self::assertStringNotContainsString('Bilan personnalisé', $html);
        $GLOBALS['__phpunit_clh_tunnel_active'] = false;
        self::assertStringContainsString('Bilan personnalisé', $this->render());
    }

    private function render(): string
    {
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/clh-petitions-grid.php';

        return ob_get_clean();
    }

    public static function campaigns(): iterable
    {
        yield 'closed campaign extended into January' => [false, true, 'Les 1 situations de la campagne 2026'];
        yield 'closed without complete results' => [false, false, 'Les 1 situations de la campagne 2027'];
        yield 'running campaign' => [true, true, 'Les situations que nous défendons en 2027'];
    }
}
