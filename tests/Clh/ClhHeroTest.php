<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhHeroTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
        require_once __DIR__ . '/template-stand-ins.php';

        function is_admin(): bool
        {
            return false;
        }

        function amnesty_remove_first_hero_from_content(string $content): string
        {
            return $content;
        }

        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => 2026];
        $GLOBALS['__phpunit_acf_field_values'] = [2026 => [
            'change_their_history_hero_title' => 'Ancien titre',
            'change_their_history_hero_description' => 'Ancien chapeau',
            'change_their_history_hero_button_label' => 'Ancien bouton',
        ]];
    }

    #[DataProvider('campaigns')]
    public function testHeroUsesFixedCopyAndOnlyShowsDaysDuringTheCampaign(bool $active, int $days): void
    {
        $GLOBALS['__phpunit_clh_tunnel_active'] = $active;
        // Keep well away from a day boundary so the real helper returns exactly $days.
        $GLOBALS['__phpunit_acf_field_values'][2026]['end_date_highlight_clh'] = date('Y-m-d H:i:s', time() + $days * DAY_IN_SECONDS - 3600);
        ob_start();
        require dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/patterns/page-change-their-history-hero.php';
        $html = ob_get_clean();
        $hero = strstr($html, '</section>', true);

        self::assertStringContainsString('>Changez leur</span>', $hero);
        self::assertStringContainsString('is-marked">histoire</span>', $hero);
        self::assertStringContainsString(esc_html("Partout dans le monde, des personnes résistent à l'injustice et à l'impunité, souvent elles payent le prix de leur courage. Elles ne doivent pas résister seules. Avec votre signature, soutenez-les."), $hero);
        self::assertStringNotContainsString('Ancien', $hero);
        self::assertStringNotContainsString('<a ', $hero);
        self::assertStringNotContainsString('clh-signatures', $hero);
        self::assertStringNotContainsString('data-countdown', $html);
        if ($active) {
            $label = $days === 1 ? '1 jour pour agir' : "$days jours pour agir";
            self::assertStringContainsString($label, $hero);
            self::assertLessThan(strpos($hero, '<h1'), strpos($hero, 'class="clh-hero-days"'));
        } else {
            self::assertStringNotContainsString('clh-hero-days', $hero);
        }
    }

    public static function campaigns(): iterable
    {
        yield 'running' => [true, 12];
        yield 'last day' => [true, 1];
        yield 'ended' => [false, 0];
    }
}
