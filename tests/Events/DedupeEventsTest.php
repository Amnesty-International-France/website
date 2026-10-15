<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../wp-content/themes/humanity-theme/commands/dedupe-events.php';

/**
 * MAINT-323: events imported several times from Prismic must be grouped on
 * their content, so that yearly editions sharing a title are left alone.
 */
final class DedupeEventsTest extends TestCase
{
    /**
     * Build an event row as amnesty_dedupe_events_fetch() returns it.
     *
     * @param array<string,mixed> $fields
     */
    private function event(array $fields): object
    {
        return (object) array_merge([
            'ID'            => '1',
            'post_name'     => 'evenement',
            'post_title'    => 'Foire aux livres et expo à Chateau-Thierry',
            'post_content'  => '<p>Le groupe organise une foire aux livres.</p>',
            'post_status'   => 'publish',
            'post_date'     => '2024-10-01 10:00:00',
            'post_modified' => '2025-12-05 10:00:00',
            'start_date'    => '2024-11-02 05:57:00',
            'end_date'      => '2024-11-03 05:57:00',
            'venue'         => '8 rue du château, 02400 Château-Thierry',
            'occurrences'   => '1',
        ], $fields);
    }

    /**
     * foire-aux-livres-et-expo-a-chateau-thierry-2 / -3 on production: every
     * field matches, only the ID and the slug suffix differ.
     */
    public function testImportedCopiesAreGrouped(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '75400', 'post_name' => 'foire-aux-livres-et-expo-a-chateau-thierry-2' ]),
            $this->event([ 'ID' => '90124', 'post_name' => 'foire-aux-livres-et-expo-a-chateau-thierry-3', 'post_modified' => '2026-01-14 10:00:00' ]),
        ]);

        $this->assertCount(1, $groups);
        $this->assertSame([ '75400', '90124' ], array_column(reset($groups), 'ID'));
    }

    /**
     * foire-aux-livres-mably / mably1 / mably2 on production: same title, but
     * the 2017, 2018 and 2021 editions.
     */
    public function testYearlyEditionsSharingATitleAreNotGrouped(): void
    {
        $title = 'Foire aux livres à Mably';

        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '84296', 'post_name' => 'foire-aux-livres-mably', 'post_title' => $title, 'start_date' => '2017-11-18 09:00:00', 'post_content' => 'Édition 2017' ]),
            $this->event([ 'ID' => '82373', 'post_name' => 'foire-aux-livres-mably1', 'post_title' => $title, 'start_date' => '2018-11-16 23:00:00', 'post_content' => 'Édition 2018' ]),
            $this->event([ 'ID' => '78918', 'post_name' => 'foire-aux-livres-mably2', 'post_title' => $title, 'start_date' => '2021-11-19 23:00:00', 'post_content' => 'Édition 2021' ]),
        ]);

        $this->assertSame([], $groups);
    }

    public function testSameContentOnAnotherDateIsNotADuplicate(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '1' ]),
            $this->event([ 'ID' => '2', 'start_date' => '2025-11-02 05:57:00', 'end_date' => '2025-11-03 05:57:00' ]),
        ]);

        $this->assertSame([], $groups);
    }

    public function testDifferentVenueIsNotADuplicate(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '1' ]),
            $this->event([ 'ID' => '2', 'venue' => 'Salle des fêtes, Château-Thierry' ]),
        ]);

        $this->assertSame([], $groups);
    }

    public function testMarkupAndWhitespaceDoNotSplitAGroup(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '1', 'post_content' => '<p>Le groupe organise une foire aux livres.</p>' ]),
            $this->event([ 'ID' => '2', 'post_content' => "<!-- wp:paragraph -->\n<p class=\"wp-block-paragraph\">Le groupe  organise une foire aux livres.</p>\n<!-- /wp:paragraph -->" ]),
        ]);

        $this->assertCount(1, $groups);
    }

    public function testHtmlEntitiesDoNotSplitAGroup(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '1', 'venue' => 'Place de la légion d’honneur, 78120 Rambouillet' ]),
            $this->event([ 'ID' => '2', 'venue' => 'Place de la légion d&rsquo;honneur, 78120 Rambouillet' ]),
        ]);

        $this->assertCount(1, $groups);
    }

    public function testOriginalWithoutOccurrenceJoinsItsCopies(): void
    {
        $groups = amnesty_dedupe_events_group([
            $this->event([ 'ID' => '60000', 'post_name' => 'expo-femmes-rambouillet', 'occurrences' => '0' ]),
            $this->event([ 'ID' => '76486', 'post_name' => 'expo-femmes-rambouillet-2' ]),
            $this->event([ 'ID' => '91210', 'post_name' => 'expo-femmes-rambouillet-3' ]),
        ]);

        $this->assertCount(3, reset($groups));
    }

    /**
     * Agreed on MAINT-323: keep the oldest event, i.e. the original with the
     * unsuffixed URL, even though it answers 404. The copies come in reverse
     * order on purpose, so returning the input unsorted would fail.
     */
    public function testKeeperIsTheOriginalEvenWithoutAnOccurrence(): void
    {
        $keeper = amnesty_dedupe_events_pick_keeper([
            $this->event([ 'ID' => '91210', 'post_name' => 'expo-femmes-rambouillet-3' ]),
            $this->event([ 'ID' => '76486', 'post_name' => 'expo-femmes-rambouillet-2' ]),
            $this->event([ 'ID' => '60000', 'post_name' => 'expo-femmes-rambouillet', 'occurrences' => '0' ]),
        ]);

        $this->assertSame('60000', $keeper->ID);
    }

    public function testKeeperComparesIdsAsNumbers(): void
    {
        $keeper = amnesty_dedupe_events_pick_keeper([
            $this->event([ 'ID' => '100000' ]),
            $this->event([ 'ID' => '99999' ]),
        ]);

        $this->assertSame('99999', $keeper->ID);
    }

    public function testEventWithoutOccurrenceNeedsRepair(): void
    {
        $this->assertTrue(amnesty_dedupe_events_needs_repair($this->event([ 'occurrences' => '0' ])));
        $this->assertFalse(amnesty_dedupe_events_needs_repair($this->event([ 'occurrences' => '1' ])));
    }

    /**
     * Without the TEC custom tables, occurrences is NULL: nothing to rebuild.
     */
    public function testNothingNeedsRepairWhenTheTableIsMissing(): void
    {
        $this->assertFalse(amnesty_dedupe_events_needs_repair($this->event([ 'occurrences' => null ])));
    }
}
