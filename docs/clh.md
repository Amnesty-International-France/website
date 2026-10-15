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
  - `start_date_highligth_clh` and `end_date_highlight_clh`: the campaign window.
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
the hard-coded bodies without changing them. Each hard-coded body is marked with a
`TODO CLH-` comment.

### `amnesty_get_clh_campaign_figures(): array`

Status: hard-coded (TODO CLH-28, CLH-1).

Figures of the current campaign, for the banner and the progress indicator (CLH-28,
CLH-1).

| Key | Type | Meaning |
|---|---|---|
| `days_remaining` | `int` | Days left before the end date; 1 on the last day. |
| `seconds_remaining` | `int` | Seconds left before the end date, for a live countdown. |
| `span_days` | `int` | Length of the campaign in days, from start to end date. |
| `signatures` | `int` | Signatures of all the campaign petitions. |
| `target` | `int` | Sum of the targets of all the campaign petitions; 0 without petitions. |
| `percentage` | `int` | `signatures` / `target`, from 0 to 100; capped at 100, 0 when `target` is 0. |

### `amnesty_get_clh_grid_petitions(): array`

Status: hard-coded (TODO CLH-31).

Petitions of the grid, ordered by end date (closest first), at most 10 (CLH-31). An
empty list when the campaign has no petitions.

| Key | Type | Meaning |
|---|---|---|
| `id` | `int` | Petition post ID. |
| `title` | `string` | Petition title. |
| `short_title` | `string` | Short title, for mobile; falls back to `title`. |
| `image_id` | `int` | Attachment ID of the photo; 0 without photo. |
| `signatures` | `int` | Signatures of the petition. |
| `target` | `int` | Signature target of the petition. |
| `percentage` | `int` | `signatures` / `target`, from 0 to 100. |
| `url` | `string` | Petition page ("Voir la pétition"). |
| `sign_url` | `string` | Entry into the signature tunnel on this petition ("Signer la pétition"). |

### `amnesty_get_clh_testimonials(): array`

Status: hard-coded (TODO CLH-34).

Testimonials of the "Ils vous disent merci" block (CLH-33, CLH-34), grouped by year:
`array<int $year, list<testimonial>>`, oldest year first. An empty array without
testimonials. Each testimonial:

| Key | Type |
|---|---|
| `name` | `string` |
| `country` | `string` |
| `activity` | `string` |
| `quote` | `string` |
| `description` | `string` |
| `image_id` | `int`, 0 without photo |

### `amnesty_get_clh_donation_url(): string`

Status: hard-coded (TODO CLH-23).

Donation link of the page, carrying the page's own origin code (CLH-23, CLH-35).

### `amnesty_get_clh_campaign_final_results(): ?array`

Status: not available yet (CLH-29).

End-of-campaign results entered in the back-office, for the indicator of an ended
campaign (CLH-29, CLH-1). `null` when there is no campaign page or when one of the two
values is missing.

| Key | Type | Meaning |
|---|---|---|
| `signatures` | `int` | Final signature count; 0 is a valid value. |
| `year` | `int` | Campaign year. |

## Testing the page

Two temporary URL parameters, without any access check, force the page into a given
situation. They combine: `?clh_state=ended&clh_scenario=no_petitions`.

- `clh_state` forces the campaign state, site-wide (it is read by
  `amnesty_is_clh_petition_tunnel_active()`, so it also opens or closes the tunnel):
  `in_progress` or `ended`.
- `clh_scenario` picks the data returned by the hard-coded helpers:

| Scenario | Effect |
|---|---|
| `no_petitions` | No petition: empty grid, figures at 0. |
| `few_petitions` | 6 petitions in the grid. |
| `no_photo` | One petition and one testimonial without photo. |
| `last_day` | 1 day remaining. |
| `goal_exceeded` | More signatures than the target. |
| `no_testimonials` | No testimonial. |
| `single_testimonial` | One testimonial only. |

The hard-coded petitions and testimonials show the latest image of the media library;
without any image in the library, they have no photo.
