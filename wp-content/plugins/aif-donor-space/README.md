# AIF Donor Space Plugin

This document describes the `wp-content/plugins/aif-donor-space` subsystem.

## Role

`aif-donor-space` provides the business services used by the authenticated donor
and member area known as `Mon Espace`.

The plugin is responsible for:

- ensuring the required login, account creation, verification, and `Mon Espace`
  pages exist;
- exposing Salesforce helper functions for donor/member data;
- managing the Salesforce contact identifier stored on WordPress users;
- sending verification and password reset emails through Mailgun;
- providing two-factor email verification helpers;
- creating Salesforce `Case` records for contact requests, tax receipt duplicate
  requests, and IBAN update requests;
- exposing a REST endpoint for duplicate tax receipt requests;
- loading donor-space CSS and small JavaScript helpers;
- providing shared PHP partials used by theme templates.

The plugin does not render the whole donor space by itself. Most page templates
and page-specific form handlers still live in `wp-content/themes/humanity-theme`
and call functions provided by this plugin.

## Directory Layout

Important files and directories:

- `aif-donor-space.php`: plugin header, constants, module loading, page creation,
  and asset enqueueing.
- `configuration.php`: requirement checks, parent/theme style loading, admin bar
  behavior, and partial loading helper.
- `includes/authorization.php`: access guard for authenticated `Mon Espace`
  pages.
- `includes/sales-force/`: Salesforce authentication and data helpers.
- `includes/domain/2FA/`: email verification code generation, storage, sending,
  and attempt limiting.
- `includes/domain/bank/`: IBAN and SEPA mandate helpers.
- `includes/domain/contact/`: Salesforce contact request creation.
- `includes/domain/tax-receipt/`: tax receipt grouping helpers, duplicate
  request creation, and REST controller.
- `includes/domain/user-authentification.php`: password reset token and Mailgun
  email helper.
- `includes/utils.php`: partial include helper.
- `templates/partials/`: reusable PHP partials consumed by theme pages and
  patterns.
- `templates/check-email.php`: legacy page template.
- `assets/css/` and `assets/js/`: static plugin assets enqueued on the
  frontend.

## Bootstrap

The plugin defines:

- `AIF_DONOR_SPACE_PATH`;
- `AIF_DONOR_SPACE_VERSION`;
- `AIF_DONOR_SPACE_URL`.

It then loads configuration, authorization, Salesforce helpers, domain modules,
and utilities through `require_once`.

The plugin hooks into WordPress with:

- `init`: periodically ensures critical pages exist;
- `after_switch_theme`: creates the same critical pages after theme activation;
- `wp_enqueue_scripts`: loads plugin frontend CSS and JavaScript;
- `admin_notices`: checks required Salesforce and Mailgun environment variables;
- `rest_api_init`: registers the tax receipt duplicate request endpoint.

## Created Pages

`aif_donor_space_create_pages()` creates or ensures the following page tree:

- `connectez-vous`;
- `creer-votre-compte`;
- `verifier-votre-email`;
- `mot-de-passe-oublie`;
- `mon-espace`;
- `mon-espace/actualites`;
- `mon-espace/agir-et-se-mobiliser`;
- `mon-espace/agir-et-se-mobiliser/actualites-militantes`;
- `mon-espace/agir-et-se-mobiliser/nos-petitions`;
- `mon-espace/vie-democratique`;
- `mon-espace/vie-democratique/actualites-democratiques`;
- `mon-espace/vie-democratique/ressources-vie-democratique`;
- `mon-espace/boite-a-outils`;
- `mon-espace/boite-a-outils/ressources-militants`;
- `mon-espace/boite-a-outils/se-former`;
- `mon-espace/mes-dons`;
- `mon-espace/mes-dons/mes-informations-personnelles`;
- `mon-espace/mes-dons/mes-recus-fiscaux`;
- `mon-espace/mes-dons/mes-demandes`;
- `mon-espace/mes-dons/nous-contacter`;
- `mon-espace/mon-compte`;
- `mon-espace/mon-compte/se-deconnecter`.

The `init` hook protects this page creation with the
`aif_critical_pages_check_lock` transient for five minutes.

## Theme Integration

The theme remains the main rendering layer for the donor space.

Important theme integration points:

- `includes/my-space/template.php` detects pages under `mon-espace`, chooses the
  specific PHP or HTML template, and calls `check_user_page_access()`.
- `page-connectez-vous.php` uses plugin Salesforce helpers, stores the
  Salesforce contact ID on successful login, and renders the login form.
- `page-creer-votre-compte.php` checks Salesforce membership/donor eligibility,
  creates the WordPress user, generates a 2FA code, and sends it through
  Mailgun.
- `page-verifier-votre-email.php` verifies the 2FA code and can send a new one.
- `page-mes-dons.php`, `page-mes-informations-personnelles.php`,
  `page-mes-recus-fiscaux.php`, `page-mes-demandes.php`, and
  `page-modification-coordonnees-bancaire.php` call the plugin Salesforce and
  domain helpers.
- `patterns/contact-us.php` creates Salesforce contact requests through the
  plugin.
- several theme pages and patterns render shared plugin partials with
  `aif_include_partial()`.

Because the theme directly calls plugin functions, the donor-space templates
expect this plugin to be active.

Cross-subsystem documentation: [Mon Espace](../../../docs/MON-ESPACE.md).

## Salesforce Integration

Salesforce authentication uses the client credentials flow.

Required variables:

- `AIF_SALESFORCE_URL`;
- `AIF_SALESFORCE_CLIENT_ID`;
- `AIF_SALESFORCE_SECRET`.

The access token is cached in WordPress options:

- `salesforce_access_token`;
- `salesforce_token_expiration_time`.

The plugin exposes generic helpers:

- `get_salesforce_data_donor_space()`;
- `post_salesforce_data_donor_space()`;
- `patch_salesforce_data_donor_space()`.

It also exposes donor-space specific helpers for:

- member lookup by email;
- Contact lookup by Salesforce ID;
- Contact update;
- tax receipt retrieval;
- SEPA mandate retrieval;
- demand retrieval;
- donor/member access checks.

The Salesforce contact ID is stored as WordPress user meta under `user_SF_ID`.

## Mailgun Integration

Mailgun is used to send:

- 2FA verification emails;
- password reset emails.

Required variables:

- `AIF_MAILGUN_TOKEN`;
- `AIF_MAILGUN_URL`;
- `AIF_MAILGUN_DOMAIN`.

The plugin sends Mailgun template emails with `wp_remote_post()`.

## Authentication And Access

`check_user_page_access()` protects authenticated `Mon Espace` pages. It:

- redirects anonymous users to `connectez-vous` with a `redirect_to` query
  parameter;
- loads the current user's Salesforce member data;
- redirects users without Salesforce data;
- redirects users without donor/member access.

The theme also applies member-specific restrictions in
`includes/my-space/template.php`: non-members are redirected to the donor area
and can only access a reduced list of `Mon Espace` pages.

The 2FA module stores verification state and login attempt state in user meta:

- `2fa_code`;
- `email-verfied`;
- `login_attempts`;
- `login_blocked_until`.

## REST API

The plugin registers:

- namespace: `aif-donor-space/v1`;
- route: `/duplicate-tax-receipt-request/`;
- method: `POST`;
- callback: `handle_duplicate_tax_receipt_request()`;
- permission callback: `check_nonce()`.

The endpoint requires:

- a valid `X-WP-Nonce` header for `wp_rest`;
- an authenticated WordPress user;
- a stored Salesforce contact ID;
- a `taxReceiptReference` JSON parameter.

On success, it creates a Salesforce `Case` requesting a duplicate tax receipt.

## Frontend Assets

The plugin enqueues these assets on the frontend:

- `assets/css/style.css`;
- `assets/js/check-password.js`;
- `assets/js/display-password.js`;
- `assets/js/dropdown.js`;
- `assets/js/iban-formatter.js`;
- `assets/js/create-duplicate-tax-receipt-demand.js`.

The duplicate tax receipt script receives `aifDonorSpace.nonce` and
`aifDonorSpace.root` through `wp_localize_script()`.

These assets are static files in the plugin. They are not built by the
`private` Webpack pipeline.

## Environment Checks

On admin pages, `aif_donor_space_check_requirements()` checks the required
Salesforce and Mailgun variables for administrators. If one is missing, it shows
an admin notice and calls `deactivate_plugins('aif-donor-space')`.

## Known Weak Points

- The plugin provides business services, but the theme still owns most page
  rendering and form controllers.
- Theme templates call plugin functions directly, so plugin deactivation can
  break authenticated-area pages.
- Salesforce and Mailgun availability directly affect login, account creation,
  verification, password reset, and donor/member pages.
- Local fixtures do not currently model full Salesforce and Mailgun behavior for
  donor/member flows.
- Browser-triggered REST endpoints should keep explicit nonce and authentication
  checks when they are changed.

## Change Conventions

When changing this plugin:

- keep service helpers in the plugin and page rendering in the theme unless the
  subsystem boundary is intentionally changed;
- check theme templates when changing any public plugin function;
- keep Salesforce object field names and record type IDs explicit and reviewed
  with the business owner;
- preserve the `redirect_to` login flow used by protected `Mon Espace` pages;
- update or add REST nonce checks for browser-triggered endpoints;
- document any new required environment variable in this README and in the
  global environment documentation.

Recommended checks:

- PHP syntax for changed plugin and theme files;
- manual login/account creation/2FA flow testing in a local or staging
  environment configured with Salesforce and Mailgun credentials;
- REST endpoint testing while logged in and logged out;
- Turnstile checks when editing login, account creation, or email verification
  forms in the theme.
