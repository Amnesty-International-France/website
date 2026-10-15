<?php

declare(strict_types=1);

/**
 * Data helpers of the "Changez leur histoire" (CLH) campaign page.
 *
 * Most helpers still return hard-coded data, so the front-end can be built before the
 * logic lands. Each hard-coded body is marked with a "TODO CLH-" comment; none may
 * remain when sprint/page-clh merges into main. See docs/clh.md.
 */

if (!function_exists('amnesty_get_clh_placeholder_image_id')) {
    /**
     * Photo of the hard-coded petitions and testimonials: the latest image of the media library.
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
     * TODO CLH-28: temporary, remove it with the last hard-coded helper.
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

if (!function_exists('amnesty_get_clh_campaign_figures')) {
    /**
     * Figures of the current campaign, for the banner and the progress indicator.
     *
     * TODO CLH-28 / CLH-1: hard-coded; compute them from the campaign page and its petitions.
     *
     * @return array{days_remaining: int, seconds_remaining: int, span_days: int, signatures: int, target: int, percentage: int}
     */
    function amnesty_get_clh_campaign_figures(): array
    {
        [$days_remaining, $signatures, $target] = match (amnesty_get_clh_scenario()) {
            'no_petitions' => [12, 0, 0],
            'last_day' => [1, 1_843_250, 2_000_000],
            'goal_exceeded' => [12, 2_150_000, 2_000_000],
            default => [12, 1_243_567, 2_000_000],
        };

        return [
            'days_remaining' => $days_remaining,
            'seconds_remaining' => $days_remaining * 86_400 - 3_600,
            'span_days' => 45,
            'signatures' => $signatures,
            'target' => $target,
            'percentage' => $target > 0 ? (int) min(100, floor($signatures / $target * 100)) : 0,
        ];
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
     * Testimonials of the "Ils vous disent merci" block, grouped by year (oldest first).
     *
     * TODO CLH-34: hard-coded; read them from the block settings.
     *
     * @return array<int, list<array{name: string, country: string, activity: string, quote: string, description: string, image_id: int}>>
     *         image_id is 0 when the testimonial has no photo
     */
    function amnesty_get_clh_testimonials(): array
    {
        $scenario = amnesty_get_clh_scenario();
        $image_id = amnesty_get_clh_placeholder_image_id();

        $testimonial = static fn (string $name, string $country, bool $has_photo = true): array => [
            'name' => $name,
            'country' => $country,
            'activity' => 'Défenseure des droits humains',
            'quote' => 'Vos lettres m\'ont donné la force de tenir. Merci à chacune et chacun d\'entre vous.',
            'description' => 'Emprisonnée pour son engagement, elle a été libérée après une mobilisation internationale.',
            'image_id' => $has_photo ? $image_id : 0,
        ];

        return match ($scenario) {
            'no_testimonials' => [],
            'single_testimonial' => [2024 => [$testimonial('Personne A', 'Pays A')]],
            default => [
                2016 => [$testimonial('Personne A', 'Pays A')],
                2019 => [$testimonial('Personne B', 'Pays B')],
                2022 => [$testimonial('Personne C', 'Pays C', $scenario !== 'no_photo')],
                2024 => [
                    $testimonial('Personne D', 'Pays D'),
                    $testimonial('Personne E', 'Pays E'),
                ],
            ],
        };
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
