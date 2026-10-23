<?php

declare(strict_types=1);

/**
 * Data helpers of the "Changez leur histoire" (CLH) campaign page.
 *
 * Some helpers still return hard-coded data, so the front-end can be built before the
 * logic lands. Each hard-coded body is marked with a "TODO CLH-" comment; none may
 * remain when sprint/page-clh merges into main. See docs/clh.md.
 */

require_once dirname(__DIR__) . '/post-types/clh.php';

if (!defined('AMNESTY_CLH_DEFAULT_PETITION_TARGET')) {
    // Signature target of a petition whose own target is empty, as on the petition cards and in the tunnel.
    define('AMNESTY_CLH_DEFAULT_PETITION_TARGET', 200_000);
}

if (!defined('AMNESTY_CLH_GRID_MAX_PETITIONS')) {
    // Cards of the campaign grid: two rows of five on desktop.
    define('AMNESTY_CLH_GRID_MAX_PETITIONS', 10);
}

if (!function_exists('amnesty_get_clh_petition_target')) {
    /**
     * Signature target of a petition, AMNESTY_CLH_DEFAULT_PETITION_TARGET when empty.
     *
     * @param int $petition_id the petition ID
     *
     * @return int
     */
    function amnesty_get_clh_petition_target(int $petition_id): int
    {
        return (int) get_field('objectif_signatures', $petition_id) ?: AMNESTY_CLH_DEFAULT_PETITION_TARGET;
    }
}

if (!function_exists('amnesty_clh_percentage')) {
    /**
     * Signatures out of a target, as a percentage rounded down and capped at 100.
     *
     * @param int $signatures the signature count
     * @param int $target     the signature target
     *
     * @return int from 0 to 100; 0 when the target is not positive
     */
    function amnesty_clh_percentage(int $signatures, int $target): int
    {
        if ($target <= 0) {
            return 0;
        }

        // Integer division: a float ratio such as 0.29 would floor to 28.
        return min(100, intdiv($signatures * 100, $target));
    }
}

if (!function_exists('amnesty_get_clh_petition_end_timestamp')) {
    /**
     * End date of a petition, from its ACF field "date_de_fin" (returned as d.m.Y), to
     * order petitions by end date.
     *
     * @param int $petition_id the petition ID
     *
     * @return int|null Unix timestamp of the start of that day, in PHP's default timezone;
     *                  null when the date is empty or invalid (e.g. 31.02.2026)
     */
    function amnesty_get_clh_petition_end_timestamp(int $petition_id): ?int
    {
        $end_date = DateTimeImmutable::createFromFormat('!d.m.Y', (string) get_field('date_de_fin', $petition_id));

        // createFromFormat() rolls an impossible day over to the next month, with a warning.
        if ($end_date === false || (DateTimeImmutable::getLastErrors()['warning_count'] ?? 0) > 0) {
            return null;
        }

        return $end_date->getTimestamp();
    }
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
            $figures['target'] += amnesty_get_clh_petition_target((int) $petition->ID);
        }

        $figures['percentage'] = amnesty_clh_percentage($figures['signatures'], $figures['target']);

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
     * Petitions of the campaign grid: the petitions tagged CLH, at most 10. Active
     * petitions come first, closest end date first; then closed ones, most recently
     * closed first; then those without a valid end date. A petition keeps its tag once
     * closed, so the closed ones of past campaigns are the first left out.
     *
     * Petitions with the same end date keep the order of amnesty_get_clh_campaign_petitions().
     * Titles are returned as entered, unescaped: escape them on output.
     *
     * @return list<array{id: int, title: string, short_title: string, image_id: int, signatures: int, target: int, percentage: int, url: string, sign_url: string}>
     *         image_id is 0 when the petition has no photo; url is '' when the
     *         permalink cannot be built
     */
    function amnesty_get_clh_grid_petitions(): array
    {
        $petitions = amnesty_get_clh_campaign_petitions();
        $now = time();
        $sort_keys = array_map(
            static function ($petition) use ($now): array {
                $end = amnesty_get_clh_petition_end_timestamp((int) $petition->ID);

                return match (true) {
                    $end === null => [2, 0],
                    // Closed as in amnesty_is_petition_not_expired(): the end date is past.
                    $end <= $now => [1, -$end],
                    default => [0, $end],
                };
            },
            $petitions,
        );

        // usort() is stable: petitions with the same end date keep the query order.
        $order = array_keys($petitions);
        usort($order, static fn (int $a, int $b): int => $sort_keys[$a] <=> $sort_keys[$b]);

        $sign_url = amnesty_get_clh_petition_tunnel_url();

        return array_map(
            static fn (int $index): array => amnesty_get_clh_grid_card($petitions[$index], $sign_url),
            array_slice($order, 0, AMNESTY_CLH_GRID_MAX_PETITIONS),
        );
    }
}

if (!function_exists('amnesty_get_clh_grid_card')) {
    /**
     * Card of a petition in the campaign grid; see amnesty_get_clh_grid_petitions().
     *
     * @param object $petition the petition post
     * @param string $sign_url entry into the signature tunnel
     *
     * @return array{id: int, title: string, short_title: string, image_id: int, signatures: int, target: int, percentage: int, url: string, sign_url: string}
     */
    function amnesty_get_clh_grid_card(object $petition, string $sign_url): array
    {
        $id = (int) $petition->ID;
        $title = (string) $petition->post_title;
        $signatures = amnesty_get_petition_signature_count($id);
        $target = amnesty_get_clh_petition_target($id);

        return [
            'id' => $id,
            'title' => $title,
            'short_title' => trim((string) get_field('short_title', $id)) ?: $title,
            'image_id' => (int) get_post_thumbnail_id($id),
            'signatures' => $signatures,
            'target' => $target,
            'percentage' => amnesty_clh_percentage($signatures, $target),
            'url' => (string) get_permalink($id),
            'sign_url' => add_query_arg('clh_petition', $id, $sign_url),
        ];
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
     * TODO CLH-23: replace CLH_PAGE_TEMP with the page's code from Périscope.
     * Keep it distinct from the tunnel's WBF01W1012 origin code.
     *
     * @return string
     */
    function amnesty_get_clh_donation_url(): string
    {
        return 'https://soutenir.amnesty.fr/b?cid=66&reserved_originecode=CLH_PAGE_TEMP';
    }
}
