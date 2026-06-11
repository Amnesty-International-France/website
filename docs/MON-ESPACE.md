# Mon Espace

`Mon Espace` is the authenticated donor and member area. It is implemented
across the main theme and the `aif-donor-space` plugin.

## Role

The theme owns page rendering, template resolution, redirects, and most page
controllers. The plugin owns reusable donor-space services: Salesforce helpers,
access checks, Mailgun email delivery, 2FA helpers, REST endpoints, critical
page creation, and shared partials.

This boundary is important: changing either side can break the other because
theme templates call plugin functions directly.

## Main Flows

Login starts on `connectez-vous`. When an anonymous visitor requests a protected
`Mon Espace` page, the access guard redirects to login with a `redirect_to`
query parameter.

Account creation starts on `creer-votre-compte`. The theme checks eligibility
through plugin Salesforce helpers, creates the WordPress user, generates a 2FA
code, and sends the verification email through Mailgun.

Email verification runs on `verifier-votre-email`. Verification state and retry
state are stored as WordPress user meta.

Authenticated donor/member pages live under `/mon-espace`. The theme resolves a
specific PHP template first, then a matching HTML template, then a default
`Mon Espace` template.

Member-only areas are restricted by the theme. Non-members can still access the
donor-focused subset of pages such as donations, tax receipts, requests,
contact, and account pages.

## Data Ownership

WordPress users represent local authenticated accounts. The Salesforce contact
identifier is stored as user meta under `user_SF_ID`.

Salesforce remains the source for donor/member data. The site fetches member
status, contact data, donations, tax receipts, SEPA mandates, and demands
through plugin helpers.

Mailgun is used for account verification and password reset emails.

## External Integrations

`Mon Espace` depends on:

- Salesforce credentials and donor/member APIs;
- Mailgun credentials and templates;
- Cloudflare Turnstile on sensitive public auth/account forms;
- the `aif-donor-space` plugin being active;
- theme templates under `wp-content/themes/humanity-theme`.

## Known Weak Points

- Rendering and business services are split across theme and plugin without a
  hard interface layer.
- The plugin deactivates itself from an admin notice when required environment
  variables are missing; that can make theme templates that call plugin
  functions fail.
- The page tree is created from plugin code, but most templates live in the
  theme.
- Salesforce availability affects login, access checks, and donor/member pages.
- Mailgun availability affects account verification and password reset flows.
- User meta keys include historical spelling such as `email-verfied`; preserve
  compatibility unless a migration is added.
- Local E2E fixtures do not currently model full Salesforce and Mailgun donor
  behavior.

## Code Map

Start here when changing `Mon Espace`:

- `wp-content/plugins/aif-donor-space/aif-donor-space.php`: plugin bootstrap,
  critical page creation, and asset loading.
- `wp-content/plugins/aif-donor-space/includes/authorization.php`: authenticated
  access guard used by theme templates.
- `wp-content/plugins/aif-donor-space/includes/sales-force/`: donor-space
  Salesforce helpers.
- `wp-content/plugins/aif-donor-space/includes/domain/2FA/`: verification code
  generation and email sending.
- `wp-content/plugins/aif-donor-space/includes/domain/user-authentification.php`:
  password reset token and email helper.
- `wp-content/themes/humanity-theme/includes/my-space/template.php`: template
  selection and member/non-member redirects.
- `wp-content/themes/humanity-theme/page-*.php`: login, account creation,
  verification, password, donation, tax receipt, request, and account pages.
- `wp-content/themes/humanity-theme/templates/page-my-space-default.html`:
  fallback authenticated-area template.

## Change Checklist

Before shipping changes in this area:

- test anonymous access to a protected page and verify `redirect_to`;
- test login, account creation, email verification, and password reset in an
  environment with safe Salesforce and Mailgun credentials or explicit mocks;
- test member and non-member redirects;
- check Turnstile behavior on edited auth/account forms;
- verify the donor-space plugin remains active with required environment
  variables;
- check REST nonce behavior when editing browser-triggered endpoints.
