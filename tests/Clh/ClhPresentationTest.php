<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClhPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/wp-content/themes/humanity-theme/includes/clh/presentation.php';
    }

    private function column(string $html): array
    {
        return ['blockName' => 'core/column', 'innerBlocks' => [], 'innerHTML' => '<div>' . $html . '</div>'];
    }

    private function row(array $columns): array
    {
        return [
            'blockName' => 'core/columns',
            'attrs' => ['className' => 'alignwide clh-situations'],
            'innerBlocks' => $columns,
            'innerHTML' => '<div class="wp-block-columns clh-situations"></div>',
            'innerContent' => ['<div class="wp-block-columns clh-situations">', null, "\n", null, '</div>'],
        ];
    }

    public function testRemovingThePhotoLeavesOnlyTheTextColumnWithoutChangingSavedContent(): void
    {
        $text = $this->column('<h2>Les situations</h2><p>Un <a href="/campagne/">lien</a>.</p>');
        $saved = $this->row([$text, $this->column("\n<p>&nbsp; </p>")]);
        $rendered = amnesty_clh_prepare_presentation($saved);

        self::assertSame([$text], $rendered['innerBlocks']);
        self::assertSame(['<div class="wp-block-columns clh-situations">', null, "\n", '</div>'], $rendered['innerContent']);
        self::assertCount(2, $saved['innerBlocks']);
    }

    public function testRemovingTextKeepsTheImageInTheCorrectRenderSlot(): void
    {
        $photo = $this->column('<figure><img src="/photo.jpg" alt="" /></figure>');
        $rendered = amnesty_clh_prepare_presentation($this->row([$this->column('<p><br></p>'), $photo]));

        self::assertSame([$photo], $rendered['innerBlocks']);
        self::assertSame(['<div class="wp-block-columns clh-situations">', "\n", null, '</div>'], $rendered['innerContent']);
    }

    public function testEmptyNestedBlocksAndAnUnselectedImageLeaveNoSection(): void
    {
        $empty = $this->column('');
        $empty['innerBlocks'] = [
            ['blockName' => 'core/group', 'innerBlocks' => [
                ['blockName' => 'core/paragraph', 'innerHTML' => '<p> </p>'],
            ]],
            ['blockName' => 'core/image', 'innerHTML' => ''],
        ];
        $rendered = amnesty_clh_prepare_presentation($this->row([$empty, $this->column('')]));

        self::assertSame([], $rendered['innerBlocks']);
        self::assertSame([], $rendered['innerContent']);
        self::assertSame('', $rendered['innerHTML']);
    }

    public function testCompleteSectionsAndOtherColumnsAreUnchanged(): void
    {
        $complete = $this->row([$this->column('<p>Texte</p>'), $this->column('<img src="/photo.jpg">')]);
        self::assertSame($complete, amnesty_clh_prepare_presentation($complete));

        $other = $this->row([$this->column(''), $this->column('')]);
        $other['attrs']['className'] = 'other-clh-situations';
        self::assertSame($other, amnesty_clh_prepare_presentation($other));
    }

    public function testDynamicBlocksAreNotDiscardedBecauseTheirSavedHtmlIsEmpty(): void
    {
        $column = $this->column('');
        $column['innerBlocks'] = [['blockName' => 'amnesty-core/button', 'innerHTML' => '']];

        self::assertTrue(amnesty_clh_presentation_has_content($column));
    }
}
