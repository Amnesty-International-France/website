# Changez leur histoire (CLH)

This file documents the helpers of the "Changez leur histoire" page: what the theme
exposes, and how to read it from a template or a block.

## The campaign in a nutshell

"Changez leur histoire" is Amnesty's yearly petition campaign. The same WordPress page
is reused every year: its slug stays `changez-leur-histoire`, its content is updated for
each edition, and it is unpublished between two campaigns while the next one is
prepared.

The page has two states:

- **in progress**: the campaign is running; visitors see the days remaining, the
  signature progress, and can enter the signature tunnel;
- **ended**: the campaign is over; visitors see its final results, with no call to sign
  and no day counter.

`amnesty_is_clh_petition_tunnel_active()` returns `true` while the campaign is in
progress, and `false` once it has ended or before it starts.

## Where the data lives

All the code lives in the theme, `wp-content/themes/humanity-theme`.

- **Campaign page**: the `changez-leur-histoire` page carries the campaign settings as
  ACF fields (group "Highlight", `includes/post-types/clh.php`):
  - `highlight_clh`: the "Temps fort" switch, which turns the campaign on;
  - `start_date_highligth_clh` and `end_date_highlight_clh`: the campaign window;
  - `final_signatures_clh` and `campaign_year_clh`: the results shown once the campaign
    has ended.
  The page is found with `amnesty_get_clh_petition_campaign_page()`.
- **Campaign petitions**: a petition belongs to the campaign when its ACF field
  `clh_petition` is checked. Each petition has a signature count
  (`amnesty_get_petition_signature_count()`), a target (`objectif_signatures`), an end
  date (`date_de_fin`) and a short title (`short_title`).
- **Signature tunnel**: sub-pages of the campaign page (`campagne`, `merci`), active only
  while the campaign is in progress.

The existing campaign helpers are in `includes/post-types/petitions.php`; the page
helpers below are in `includes/clh/helpers.php`.

## Existing helpers

| Function | Returns |
|---|---|
| `amnesty_get_clh_petition_campaign_page()` | The campaign page (`WP_Post`), or `null` if it does not exist. |
| `amnesty_get_active_clh_campaign_for_page(int $page_id)` | The campaign page when "Temps fort" is on and the current time is within the campaign window; `null` otherwise. |
| `amnesty_is_clh_petition_tunnel_active()` | `true` when the campaign is in progress, so the tunnel is open. |
| `amnesty_get_clh_campaign_petitions()` | The published petitions of the campaign (`WP_Post[]`). |
| `amnesty_get_petition_signature_count($post_id)` | The signature count of a petition (`int`). |

## Page helpers

Their names and return shapes are the ones the page can rely on: the real logic replaces
the hard-coded bodies without changing them, except `amnesty_get_clh_testimonials()`,
which takes the block attributes. Each hard-coded body is marked with a `TODO CLH-`
comment.

### `amnesty_get_clh_campaign_figures(): array`

Status: implemented.

Figures of the current campaign, for the banner and the progress indicator (CLH-28,
CLH-1). All the values are 0 when there is no campaign page.

| Key | Type | Meaning |
|---|---|---|
| `days_remaining` | `int` | Days left before the end date, rounded up: 1 on the last day; 0 when the campaign is not in progress. |
| `seconds_remaining` | `int` | Seconds left before the end date, for a live countdown; 0 when the campaign is not in progress. |
| `span_days` | `int` | Length of the campaign in days, from start to end date, rounded up; 0 when a date is missing or the end is not after the start. |
| `signatures` | `int` | Signatures of all the campaign petitions. |
| `target` | `int` | Sum of the targets of all the campaign petitions, a petition without target counting 200,000; 0 without petitions. |
| `percentage` | `int` | `signatures` / `target`, rounded down, from 0 to 100; capped at 100, 0 when `target` is 0. |

`signatures`, `target` and `percentage` are computed in both states.

### `amnesty_get_clh_grid_petitions(): array`

Status: implemented; not called by a template yet.

Petitions of the grid (CLH-31): the campaign petitions, active or closed, in both states
of the page, at most 10. Ordered by end date (`date_de_fin`): active petitions first,
closest end date first; then closed ones, most recently closed first; then those without
a valid end date. A petition keeps its tag once closed, so the closed petitions of past
campaigns are the first left out. Petitions with the same end date keep the order of
`amnesty_get_clh_campaign_petitions()`. An empty list when the campaign has no petitions.
Titles are returned as entered, unescaped: escape them on output (`esc_html()`).

| Key | Type | Meaning |
|---|---|---|
| `id` | `int` | Petition post ID. |
| `title` | `string` | Petition title. |
| `short_title` | `string` | Short title, for mobile; falls back to `title`. |
| `image_id` | `int` | Attachment ID of the photo; 0 without photo. |
| `signatures` | `int` | Signatures of the petition. |
| `target` | `int` | Signature target of the petition; 200,000 when empty. |
| `percentage` | `int` | `signatures` / `target`, rounded down, from 0 to 100. |
| `url` | `string` | Petition page ("Voir la pétition"); `''` when the permalink cannot be built. |
| `sign_url` | `string` | Signature tunnel ("Signer la pétition"). The tunnel does not open on a chosen petition yet: it picks one. |

### `amnesty_get_clh_testimonials(array $attributes): array`

Status: implemented; not called by a block yet.

Testimonials of the "Ils vous disent merci" block (CLH-33, CLH-34), read from the
`testimonials` attribute of the `amnesty-core/clh-testimonials` block. Each entry of that
attribute holds `year` (digit string as the editor stores it, or int), `name`, `country`,
`activity`, `quote`, `description` (strings) and `imageId` (attachment ID); the editor
requires them all and validates them
(`private/src/scripts/editor/clh/testimonial-validation.js`).

Returns `array<int $year, list<testimonial>>`, one key per year from the oldest
testimonial's year to the current year, oldest first; a year without testimonial maps to
`[]`. Testimonials keep their entry order within a year. Testimonials whose year is not
written with digits, or falls outside `amnesty_get_clh_campaign_year_range()` (from
`AMNESTY_CLH_FIRST_CAMPAIGN_YEAR` to the current year), are ignored. An empty array
without testimonials. A missing value, which the editor prevents, becomes `''` or 0. Texts are
returned as entered, unescaped: escape them on output (`esc_html()`). Each testimonial:

| Key | Type |
|---|---|
| `name` | `string` |
| `country` | `string` |
| `activity` | `string` |
| `quote` | `string` |
| `description` | `string` |
| `image_id` | `int`, attachment ID of the photo |

### `amnesty_get_clh_donation_url(): string`

Status: hard-coded (TODO CLH-23).

Donation link of the page, carrying the page's own origin code (CLH-23, CLH-35).

### `amnesty_get_clh_campaign_final_results(): ?array`

Status: implemented.

End-of-campaign results entered in the back-office, for the indicator of an ended
campaign (CLH-29, CLH-1). `null` when there is no campaign page, or when one of the two
values is missing or not a whole number.

| Key | Type | Meaning |
|---|---|---|
| `signatures` | `int` | Final signature count; 0 is a valid value. |
| `year` | `int` | Campaign year. |

## Progress indicator

The sticky "Temps fort" indicator (CLH-1) reads existing helpers only:

- state: `amnesty_is_clh_petition_tunnel_active()`;
- in progress: `amnesty_get_clh_campaign_figures()` (`days_remaining`, `signatures`,
  `target`, `percentage`); the "Je signe" button links to
  `amnesty_get_clh_petition_tunnel_url()`, and the tunnel picks the petition;
- ended: `amnesty_get_clh_campaign_final_results()`; `null` means no indicator at all.

## Testing the page

The campaign state follows the page settings: switch "Temps fort" off, or move the end
date into the past, to see the ended campaign.

The grid shows the published petitions whose "CLH" field is checked: tag or untag
petitions, or change their end date or photo, to see its other cases.
