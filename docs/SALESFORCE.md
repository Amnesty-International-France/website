# Salesforce Integration

Salesforce is a transversal integration used by the theme and the donor-space
plugin. It is not isolated behind one service class yet, so changes should be
made with care across all call sites.

## Runtime Boundaries

Theme integration code lives under:

- `wp-content/themes/humanity-theme/includes/salesforce/`
- petition synchronization code in `includes/petitions/`
- urgent action synchronization code in `includes/urgent-action/`
- page templates under `wp-content/themes/humanity-theme/page-*.php`

The donor-space plugin also calls Salesforce for authenticated user and member
flows. See
[`wp-content/plugins/aif-donor-space/README.md`](../wp-content/plugins/aif-donor-space/README.md).

## Environment

Theme Salesforce helpers load the core credentials through `getenv()`:

```text
AIF_SALESFORCE_URL
AIF_SALESFORCE_CLIENT_ID
AIF_SALESFORCE_SECRET
```

The donor-space plugin has its own Salesforce helpers and reads the same values
from constants when defined, falling back to `getenv()`.

Additional Salesforce mapping variables are used by forms and synchronization
flows. The root README lists the known public setup variables. The current code
also reads `AIF_SALESFORCE_CODES_AUWEBAPP` in urgent-action synchronization.
Verify that target environments use the same spelling before running those
syncs.

Local `.env` loading is done by the developer WordPress configuration, not by
the Salesforce files themselves.

## Authentication

`includes/salesforce/authentification.php` implements the OAuth client
credentials flow.

The access token is cached in WordPress options:

- `salesforce_access_token`;
- `salesforce_token_expiration_time`.

The cached token is considered valid until its stored millisecond timestamp is
greater than the current time. Refreshing posts to
`AIF_SALESFORCE_URL . 'services/oauth2/token'` with the client credentials grant.
The implementation stores a local expiration timestamp equal to Salesforce
`issued_at` plus 10 minutes.

## API Helpers

The integration is split by domain:

- `data.php`: shared GET/POST/PATCH/DELETE helpers;
- `newsletter.php`: Lead lookup, creation, update, and deletion;
- `petition.php`: petition records and Bulk API signature sync;
- `case.php`: Salesforce cases;
- `user.php`: user, member, bank details, tax receipt, and donor-space data.

Most calls use `wp_remote_*` and return decoded arrays, decoded objects,
`false`, or `WP_Error` depending on the helper and error path. The shared theme
helpers only treat `WP_Error` transport failures as request errors; non-2xx
Salesforce responses are decoded and returned to callers. Callers should not
assume a single success or error shape without checking the helper they use.

## Bulk Synchronization

Petition signature synchronization uses Salesforce Bulk API ingest jobs.

The flow:

1. Create an ingest job for `Signature_de_petition__c`.
2. Serialize local signatures as CSV.
3. Upload the CSV to the job content URL.
4. Close the job with `UploadComplete`.
5. Mark local signatures as pending.
6. Poll the job state.
7. Process successful, failed, and unprocessed result CSVs back into local
   signature status.

Bulk polling sleeps between checks, so it should run from WP-CLI or another
operational context, not from a web request.

The theme currently targets Salesforce REST API `v57.0` for standard and Bulk
API calls.

## Operational Checks

Useful checks:

```bash
wp option get salesforce_token_expiration_time
wp option delete salesforce_access_token
wp option delete salesforce_token_expiration_time
```

When debugging a flow, verify first whether the failure is token acquisition,
HTTP request shape, Salesforce response shape, or local data mapping.

## Known Weak Points

- The theme and donor-space plugin keep separate Salesforce helper layers with
  similar authentication assumptions.
- Return shapes are not fully normalized across helpers; callers must check the
  helper they use before assuming success or error handling.
- Some business flows depend on Salesforce during browser requests, especially
  login and donor/member pages in `Mon Espace`.
- Petition signatures and urgent action registrations fail asynchronously during
  WP-CLI synchronization, not during the user-facing submit.
- Field names and record type IDs are business contracts; keep them explicit and
  reviewed with the business owner when they change.

## Change Conventions

When changing Salesforce behavior:

- keep credential access through environment variables;
- keep the theme helpers and donor-space helpers in sync when changing shared
  authentication assumptions;
- avoid logging secrets or bearer tokens;
- preserve `WP_Error` handling at call sites;
- test the affected business form or WP-CLI sync flow end to end;
- document new required environment variables in `.env.example`, root README,
  and this document.
