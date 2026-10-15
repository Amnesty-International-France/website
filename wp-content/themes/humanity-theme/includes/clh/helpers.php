<?php

declare(strict_types=1);

/**
 * Data helpers of the "Changez leur histoire" (CLH) campaign page.
 *
 * Most helpers still return hard-coded data, so the front-end can be built before the
 * logic lands. Each hard-coded body is marked with a "TODO CLH-" comment; none may
 * remain when sprint/page-clh merges into main. See docs/clh.md.
 */

require_once dirname(__DIR__) . '/post-types/clh.php';

if (!function_exists('amnesty_get_clh_placeholder_image_id')) {
    /**
     * Photo of the hard-coded petitions: the latest image of the media library.
     *
     * TODO CLH-31: remove it with the last hard-coded helper.
     *
     * @return int the attachment ID, 0 when the media library has no image
     */
    function amnesty_get_clh_placeholder_image_id(): int
    {
        $image_ids = get_posts([
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ]);

        return (int) ($image_ids[0] ?? 0);
    }
}

if (!function_exists('amnesty_get_clh_scenario')) {
    /**
     * Data scenario of the hard-coded helpers, chosen through the URL (?clh_scenario=).
     *
     * TODO CLH-31: temporary, remove it with the last hard-coded helper.
     *
     * @return string the scenario name, "default" when none is given
     */
    function amnesty_get_clh_scenario(): string
    {
        $scenario = $_GET['clh_scenario'] ?? '';

        if (!is_string($scenario) || !preg_match('/^[a-z_]+$/', $scenario)) {
            return 'default';
        }

        return $scenario;
    }
}

if (!defined('AMNESTY_CLH_DEFAULT_PETITION_TARGET')) {
    // Signature target of a petition whose own target is empty, as on the petition cards and in the tunnel.
    define('AMNESTY_CLH_DEFAULT_PETITION_TARGET', 200_000);
}

if (!function_exists('amnesty_get_clh_campaign_timestamps')) {
    /**
     * Start and end of the campaign window set on the campaign page.
     *
     * @param int $page_id the campaign page ID
     *
     * @return array{start: int, end: int} Unix timestamps, 0 when a date is missing or invalid
     */
    function amnesty_get_clh_campaign_timestamps(int $page_id): array
    {
        $start_date = get_field('start_date_highligth_clh', $page_id);
        $end_date = get_field('end_date_highlight_clh', $page_id);

        return [
            'start' => $start_date ? (int) strtotime((string) $start_date) : 0,
            'end' => $end_date ? (int) strtotime((string) $end_date) : 0,
        ];
    }
}

if (!function_exists('amnesty_get_clh_campaign_figures')) {
    /**
     * Figures of the current campaign, for the banner and the progress indicator.
     *
     * Days are rounded up, so the last day counts as 1. The remaining time is 0 when the
     * campaign is not in progress; the totals are computed in every state.
     *
     * @return array{days_remaining: int, seconds_remaining: int, span_days: int, signatures: int, target: int, percentage: int}
     *         all 0 when there is no campaign page; percentage is capped at 100
     */
    function amnesty_get_clh_campaign_figures(): array
    {
        $figures = array_fill_keys(['days_remaining', 'seconds_remaining', 'span_days', 'signatures', 'target', 'percentage'], 0);
        $clh_page = amnesty_get_clh_petition_campaign_page();

        if ($clh_page === null) {
            return $figures;
        }

        ['start' => $start, 'end' => $end] = amnesty_get_clh_campaign_timestamps((int) $clh_page->ID);

        if (amnesty_is_clh_petition_tunnel_active()) {
            $figures['seconds_remaining'] = max(0, $end - time());
            $figures['days_remaining'] = (int) ceil($figures['seconds_remaining'] / DAY_IN_SECONDS);
        }

        if ($start > 0 && $end > $start) {
            $figures['span_days'] = (int) ceil(($end - $start) / DAY_IN_SECONDS);
        }

        foreach (amnesty_get_clh_campaign_petitions() as $petition) {
            $figures['signatures'] += amnesty_get_petition_signature_count($petition->ID);
            $figures['target'] += (int) get_field('objectif_signatures', $petition->ID) ?: AMNESTY_CLH_DEFAULT_PETITION_TARGET;
        }

        if ($figures['target'] > 0) {
            // Integer division: a float ratio such as 0.29 floors to 28.
            $figures['percentage'] = min(100, intdiv($figures['signatures'] * 100, $figures['target']));
        }

        return $figures;
    }
}

if (!function_exists('amnesty_clh_is_digits')) {
    /**
     * Whether a value is a whole number written with digits only
     * (no sign, separator or decimal).
     *
     * @param mixed $value the value to check
     *
     * @return bool
     */
    function amnesty_clh_is_digits(mixed $value): bool
    {
        return (is_int($value) && $value >= 0) || (is_string($value) && ctype_digit($value));
    }
}

if (!function_exists('amnesty_get_clh_campaign_year_range')) {
    /**
     * Years a campaign can be dated with: from the first campaign to the current year.
     *
     * @return array{int, int} the first and the last accepted year
     */
    function amnesty_get_clh_campaign_year_range(): array
    {
        return [AMNESTY_CLH_FIRST_CAMPAIGN_YEAR, (int) wp_date('Y')];
    }
}

if (!function_exists('amnesty_get_clh_campaign_final_results')) {
    /**
     * End-of-campaign results entered in the page settings, for the indicator
     * of the ended campaign.
     *
     * @return array{signatures: int, year: int}|null null when there is no
     *         campaign page, or when one value is missing or not a whole
     *         number
     */
    function amnesty_get_clh_campaign_final_results(): ?array
    {
        $clh_page = amnesty_get_clh_petition_campaign_page();

        if ($clh_page === null) {
            return null;
        }

        $signatures = get_field('final_signatures_clh', $clh_page->ID);
        $year = get_field('campaign_year_clh', $clh_page->ID);

        if (!amnesty_clh_is_digits($signatures) || !amnesty_clh_is_digits($year)) {
            return null;
        }

        return [
            'signatures' => (int) $signatures,
            'year' => (int) $year,
        ];
    }
}

if (!function_exists('amnesty_get_clh_grid_petitions')) {
    /**
     * Petitions of the campaign grid, by end date (closest first), at most 10.
     *
     * TODO CLH-31: hard-coded; query the petitions tagged CLH.
     *
     * @return list<array{id: int, title: string, short_title: string, image_id: int, signatures: int, target: int, percentage: int, url: string, sign_url: string}>
     *         image_id is 0 when the petition has no photo
     */
    function amnesty_get_clh_grid_petitions(): array
    {
        $scenario = amnesty_get_clh_scenario();
        $image_id = amnesty_get_clh_placeholder_image_id();

        $count = match ($scenario) {
            'no_petitions' => 0,
            'few_petitions' => 6,
            default => 10,
        };

        $petitions = [];

        for ($i = 1; $i <= $count; $i++) {
            $signatures = 40_000 + $i * 17_500;
            $target = 200_000;

            $petitions[] = [
                'id' => $i,
                'title' => sprintf('Libérez la personne n° %d, emprisonnée pour avoir défendu les droits humains', $i),
                'short_title' => sprintf('Personne n° %d', $i),
                'image_id' => $scenario === 'no_photo' && $i === 2 ? 0 : $image_id,
                'signatures' => $signatures,
                'target' => $target,
                'percentage' => (int) min(100, floor($signatures / $target * 100)),
                'url' => '#',
                'sign_url' => '#',
            ];
        }

        return $petitions;
    }
}

if (!function_exists('amnesty_get_clh_testimonials')) {
    /**
     * Testimonials of the "Ils vous disent merci" block, grouped by year, from the
     * oldest testimonial's year to the current year.
     *
     * The editor validates the testimonials; this helper only normalises their shape,
     * and ignores those whose year is not a campaign year (see
     * amnesty_get_clh_campaign_year_range()). Texts are returned as entered, unescaped:
     * escape them on output.
     *
     * @param array<string, mixed> $attributes the block attributes; testimonials are
     *                                         read from "testimonials"
     *
     * @return array<int, list<array{name: string, country: string, activity: string, quote: string, description: string, image_id: int}>>
     *         a year without testimonial maps to an empty list; empty when there is
     *         no testimonial; a missing or non-scalar text is '', a missing photo is 0
     */
    function amnesty_get_clh_testimonials(array $attributes): array
    {
        [$first_year, $current_year] = amnesty_get_clh_campaign_year_range();
        $text = static fn (mixed $value): string => is_scalar($value) ? (string) $value : '';
        $by_year = [];

        foreach ((array) ($attributes['testimonials'] ?? []) as $testimonial) {
            if (!is_array($testimonial) || !amnesty_clh_is_digits($testimonial['year'] ?? null)) {
                continue;
            }

            $year = (int) $testimonial['year'];

            if ($year < $first_year || $year > $current_year) {
                continue;
            }

            $by_year[$year][] = [
                'name' => $text($testimonial['name'] ?? ''),
                'country' => $text($testimonial['country'] ?? ''),
                'activity' => $text($testimonial['activity'] ?? ''),
                'quote' => $text($testimonial['quote'] ?? ''),
                'description' => $text($testimonial['description'] ?? ''),
                'image_id' => (int) ($testimonial['imageId'] ?? 0),
            ];
        }

        if ($by_year === []) {
            return [];
        }

        $timeline = array_fill_keys(range(min(array_keys($by_year)), $current_year), []);

        return array_replace($timeline, $by_year);
    }
}

if (!function_exists('amnesty_get_clh_donation_url')) {
    /**
     * Donation link of the page, carrying the page's own origin code.
     *
     * TODO CLH-23: provisional origin code, until Périscope provides the page's code.
     *
     * @return string
     */
    function amnesty_get_clh_donation_url(): string
    {
        return 'https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=WBF01W1012';
    }
}
