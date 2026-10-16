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

Status: implemented and used by `patterns/clh-petitions-grid.php`.

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
| `sign_url` | `string` | Signature tunnel ("Signer la pétition"), with `clh_petition=<id>` to select the first eligible petition. |

### Grid display and targeted tunnel entry

The grid renders the helper's ten cards at most, with five columns from 1024 px,
two from 768 px and one below 768 px. Mobile uses `short_title` (falling back to the
full title). The heading has a yellow line above it and can be entered separately
for each campaign state in `petitions_heading_active_clh` and
`petitions_heading_closed_clh`. Empty fields keep the automatic year/count headings.

During the campaign, active petitions link to the tunnel with `clh_petition=<id>`.
The tunnel context selects this ID only among eligible campaign petitions and is
shared by the hero, redirects and content. Sign/skip redirects use the plain tunnel
URL, so the following petition is random. Invalid IDs (including array parameters)
cannot select a petition outside that eligible list.

The agreed business rules are:

- Keep the helper order: active petitions by nearest end date, then closed petitions
  by most recent end date, then petitions without a valid end date.
- Already signed, skipped or expired petitions remain excluded from tunnel selection;
  a direct request for one falls back to another eligible petition, or the final screen.
- A closed petition links to its page with “Voir la pétition”, even during a campaign.
- The button labels remain fixed. After the campaign, every card links to its petition page.
- Missing photos display the card number on a yellow background.

### `amnesty_get_clh_testimonials(array $attributes): array`

Status: implemented and rendered by the reusable `amnesty-core/clh-testimonials` block.

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

Status: used by `patterns/clh-donation-cta.php`; hard-coded (TODO CLH-23).

Donation link of the page, carrying the page's own origin code (CLH-23, CLH-35).
The provisional code `CLH_PAGE_TEMP` is distinct from the tunnel's `WBF01W1012`;
replace it with the definitive code supplied by Périscope under CLH-23.
The donation block uses fixed copy and opens the donation page in a new tab.
It is identical during and after the campaign, with no back-office fields.

### `amnesty_get_clh_campaign_final_results(): ?array`

Status: implemented.

End-of-campaign results entered in the back-office, for the indicator of an ended
campaign (CLH-29, CLH-1). `null` when there is no campaign page, or when one of the two
values is missing or not a whole number.

| Key | Type | Meaning |
|---|---|---|
| `signatures` | `int` | Final signature count; 0 is a valid value. |
| `year` | `int` | Campaign year. |

## Banner and days (CLH-28)

The banner has fixed, translated copy in `patterns/page-change-their-history-hero.php`:
"Changez leur" / "histoire" and the standfirst specified by CLH-28, in both campaign
states. The old hero ACF group is no longer registered or read; existing metadata is
left in the database. The banner has no button or signature total.

Below 768 px, the day count appears above the title, with "jours pour agir" ("jour
pour agir" for 1). At 768 px and above, the days appear only in the indicator's circle.
The title and day number use Bely; signature numbers keep the site's font.

The page reads the backend figures and campaign state on each load. There is no
periodic client-side countdown: the days, ring, labels and campaign state remain
consistent until the next load, including when the campaign ends during a visit.
Reveal animations only animate toward those server-rendered values and respect
`prefers-reduced-motion`.

## Progress indicator

The sticky "Temps fort" indicator (CLH-1) reads existing helpers only:

- state: `amnesty_is_clh_petition_tunnel_active()`;
- in progress: `amnesty_get_clh_campaign_figures()` (`days_remaining`, `signatures`,
  `target`, `percentage`); the "Je signe" button links to
  `amnesty_get_clh_petition_tunnel_url()`, and the tunnel picks the petition;
- ended: `amnesty_get_clh_campaign_final_results()`; `null` means no indicator at all.

The initial band, its sticky version and the in-flow reminder share
`partials/clh-signature-band.php`, which
passes the helpers' values to the presentation partials without recalculating targets
or percentages. A final result of 0 is valid; either missing value suppresses all three
versions. The ACF field `final_signatures_clh` is registered once, with key
`field_6a4d2c8e1b7f1`.

All three indicator variants show "sur un objectif de X signatures" and the backend
percentage, including 100% when the target is met or exceeded. The circle represents
remaining days divided by the campaign duration: it empties as the campaign progresses.

The `amnesty/clh-signature-reminder` pattern renders a second full-width band in the
page flow, immediately before "Ils vous disent merci". It uses the same figures or
final results as the first band and is visible without JavaScript. It has no reveal
animation, so it is already readable when it takes over from the sticky indicator.
Theme version 2.0.18 invalidates WordPress's theme pattern cache automatically so the
new pattern is discovered on existing installations; no manual cache clearing is needed.

The sticky indicator takes over once the initial band has scrolled above the visible
site header. It sits below that header (and the WordPress admin bar). When the reminder's
top reaches the sticky indicator's bottom, the sticky indicator becomes invisible and
inert, leaving the reminder to scroll normally. Scrolling back up restores the sticky
indicator when the reminder moves below it. No extra space is added above the timeline.
The sticky indicator stays hidden if the initial band or the reminder is missing.

Below 768 px, all three indicator variants hide the day counter and sign button; the
day counter appears above the banner title. Without JS, the first band and the reminder
remain available, while the sticky indicator stays hidden.

### Testimonials block (CLH-33)

Insert “Ils vous disent merci (CLH)” in the page content and enter its title,
subtitle and testimonials. Existing CLH-34 blocks keep their testimonials and get
the default title; the subtitle is empty until entered. There are no sample
victories in the front-end template anymore. The block is hidden when no valid
testimonial exists. Place the good-news section (see “Campaign presentation”) in the
editable content before the testimonials.

The timeline spans the oldest entered year through the current year, opens on the
latest victory, and leaves empty years non-interactive. Within a year, people keep
entry order; selecting a year resets its selection to the first person. There is
no limit of four people or thirty years in the display. On narrow screens, the
year rail scrolls horizontally and recenters the selected year. Each instance has
independent radio groups and IDs. Missing/deleted media leaves the text and the
“Victoire” label visible without an image placeholder.

On the CLH page only, the first nonempty block renders the signature reminder
immediately before itself, so the existing sticky stop follows its editorial
position. The template's old reminder and provisional timeline have been removed
to prevent duplicates. Keep the block after the campaign presentation and good
news in the editable content. Theme version 2.0.19 refreshes the pattern cache.
On other pages, the block renders without a reminder or sticky interaction.

## Campaign presentation (CLH-32)

The four sections live in the page content and use existing Gutenberg blocks.
They are identical during and after the campaign. The local page already contains
this composition; reproduce it in the existing page when deploying, since database
content is not included in Git:

1. A `core/columns` row with class `clh-situations`: heading and two paragraphs on
   the left, a `core/image` on the right. Empty columns (including empty paragraphs
   or an unselected image) are omitted at render time, centering the remaining
   column without JavaScript. Saved blocks remain available in the editor.
2. A two-column row: “Changez leur histoire”, with its two paragraphs, and
   “Votre signature a du pouvoir”. The latter contains four image/paragraph
   columns in a nested `core/columns` block with class `clh-pictos`.
   Keep the four existing PNG illustrations (`lettres`, `gouvernement`, `soutien`,
   `chaine`) from the theme's `assets/images/clh-pictos/` directory, as agreed for
   CLH-32. Use the target site's URL when inserting them, never a localhost URL.
   Their phrases are editable French paragraphs; decorative images have empty alt
   text. JavaScript hides a failed illustration and keeps its phrase visible.
3. A `core/group` section with class `clh-good-news`, containing a heading with
   class `clh-section-title`, an introduction with class `clh-good-news-standfirst`,
   and a group with class `clh-good-news-figures`. Insert four existing
   `amnesty-core/key-figure` blocks in that group. Both number (including units or
   percentages) and label are editable. Remove the whole section group to remove
   the section and its spacing.

Below 1024 px the presentation columns stack and illustrations form a 2 × 2 grid.
Key figures use a single column on small screens. Text links use standard WordPress
paragraph links. Sample copy remains editable pending Amnesty's final content;
removing the unused static pattern file belongs to CLH-39.

## Testing the page

The campaign state follows the page settings: switch "Temps fort" off, or move the end
date into the past, to see the ended campaign.

The grid shows the published petitions whose "CLH" field is checked: tag or untag
petitions, or change their end date or photo, to see its other cases.

### Local page composition

The local page at `/nous-connaitre/nos-combats/changez-leur-histoire/` uses:

1. A `core/columns` row with class `clh-situations`: campaign text and image.
2. A two-column row: campaign introduction and the four `clh-pictos` illustrations.
3. A `core/group` section with class `clh-good-news`, its heading/standfirst and four
   editable `amnesty-core/key-figure` blocks in a `clh-good-news-figures` group.
4. The `amnesty-core/clh-testimonials` block, with its campaign reminder.

The hero, petition grid, donation and share remain in the template. When deploying,
update the existing page content as well: the database edit is not carried by Git.
The previous slider, TOC, victory carousel, action cards and portrait cards are not
part of this composition.
