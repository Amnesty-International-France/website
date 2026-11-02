<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ClhTestimonialsBlockTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/petitions-stand-ins.php';
        require_once __DIR__ . '/template-stand-ins.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/helpers.php';
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/blocks/clh-testimonials/render.php';

        function wp_unique_id(string $prefix = ''): string
        {
            static $id = 0;
            return $prefix . ++$id;
        }

        function checked(mixed $checked, mixed $current = true): void
        {
            if ((string) $checked === (string) $current) {
                echo 'checked="checked"';
            }
        }

        function is_page(mixed $page = ''): bool
        {
            return $GLOBALS['__phpunit_is_clh'] ?? false;
        }

        function wp_get_attachment_image(int $id, mixed $size = 'thumbnail', bool $icon = false, array $attr = []): string
        {
            return $id === 42 ? '<img class="clh-timeline-panel-photo" src="portrait.jpg" alt="Portrait">' : '';
        }

        $GLOBALS['__phpunit_is_clh'] = false;
    }

    private function entry(int $year, string $name, int $image = 42): array
    {
        return ['year' => $year, 'name' => $name, 'country' => 'France', 'activity' => 'Artiste', 'quote' => 'Merci !', 'description' => 'Une victoire collective.', 'imageId' => $image];
    }

    private function dom(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        return new DOMXPath($dom);
    }

    public function testEmptyBlockDoesNotRender(): void
    {
        self::assertSame('', render_clh_testimonials_block([]));
        self::assertSame('', render_clh_testimonials_block(['testimonials' => [['year' => 'invalid']]]));
    }

    public function testLatestVictoryOpensAndMissingYearsAreNotInteractive(): void
    {
        $html = render_clh_testimonials_block(['testimonials' => [$this->entry(2020, 'Ancienne'), $this->entry(2024, 'Récente')]]);
        $dom = $this->dom($html);
        self::assertSame(1, $dom->query('//input[@class="clh-timeline-input"][@checked][@value="2024"]')->length);
        self::assertSame(2, $dom->query('//input[@class="clh-timeline-input"]')->length);
        self::assertSame((int) date('Y') - 2020 + 1, $dom->query('//li')->length);
        self::assertSame(0, $dom->query('//input[@class="clh-timeline-pick-input"]')->length);
        self::assertStringNotContainsString('data-clh-reminder', $html);
    }

    public function testUnlimitedPeopleKeepEntryOrderAndInstancesAreIndependent(): void
    {
        $entries = array_map(fn (int $i): array => $this->entry(2024, 'Personne ' . $i), range(1, 6));
        $attributes = ['testimonials' => $entries];
        $dom = $this->dom(render_clh_testimonials_block($attributes) . render_clh_testimonials_block($attributes));
        self::assertSame(12, $dom->query('//input[@class="clh-timeline-pick-input"]')->length);
        self::assertSame(2, $dom->query('//input[@class="clh-timeline-pick-input"][@checked][@value="0"]')->length);
        $names = [];
        foreach ($dom->query('//input[@class="clh-timeline-input"]') as $input) {
            $names[] = $input->getAttribute('name');
        }
        self::assertCount(2, array_unique($names));
        $ids = [];
        foreach ($dom->query('//*[@id]') as $node) {
            $ids[] = $node->getAttribute('id');
        }
        self::assertCount(count($ids), array_unique($ids));
        self::assertSame('Personne 1', $dom->query('//h3/span')->item(0)->textContent);
        self::assertSame('Personne 6', $dom->query('//h3/span')->item(5)->textContent);
    }

    public function testDeletedPhotoKeepsTextAndVictoryLabelAndEscapesEditorialContent(): void
    {
        $html = render_clh_testimonials_block([
            'title' => '<script>titre</script>', 'subtitle' => 'Sous-titre & suite',
            'testimonials' => [$this->entry(2024, '<b>Nom</b>', 999)],
        ]);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('Photo à fournir', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;b&gt;Nom&lt;/b&gt;', $html);
        self::assertStringContainsString('Sous-titre &amp; suite', $html);
        self::assertStringContainsString('>Victoire</span>', $html);
        self::assertStringContainsString('Une victoire collective.', $html);
    }

    public function testClhReminderPrecedesOnlyTheFirstNonEmptyTimeline(): void
    {
        $GLOBALS['__phpunit_is_clh'] = true;
        $GLOBALS['__phpunit_clh_tunnel_active'] = false;
        $GLOBALS['__phpunit_clh_campaign_page'] = (object) ['ID' => 2026];
        $GLOBALS['__phpunit_acf_field_values'] = [2026 => ['final_signatures_clh' => 200, 'campaign_year_clh' => 2024]];
        self::assertSame('', render_clh_testimonials_block([]));
        $attributes = ['testimonials' => [$this->entry(2024, 'Une personne')]];
        $first = render_clh_testimonials_block($attributes);
        $second = render_clh_testimonials_block($attributes);
        self::assertStringContainsString('data-clh-reminder', $first);
        self::assertLessThan(strpos($first, 'clh-timeline"'), strpos($first, 'data-clh-reminder'));
        self::assertStringNotContainsString('data-clh-reminder', $second);
    }
}
