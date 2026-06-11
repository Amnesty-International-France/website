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
flows:

```text
AIF_SALESFORCE_ORIGINE__C
AIF_SALESFORCE_CODE_ORIGINE__C__WEB
AIF_SALESFORCE_RECORD_TYPE_ID
AIF_SALESFORCE_CODES_AUWEBAPP
AIF_SALESFORCE_CODES_AUWEB
AIF_SALESFORCE_CODES_MILITANT
```

`AIF_SALESFORCE_URL` is concatenated directly with `services/...` paths. Keep
the target environment value in the same shape across local, staging, and
production configuration.

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
- `user.php`: Contact lookup, Contact creation/update, and activist records;
- `wp-content/plugins/aif-donor-space/includes/sales-force/`: donor/member
  data, Contact updates, tax receipts, SEPA mandates, donor requests, and
  donor-space authentication.

Most calls use `wp_remote_*` and return decoded arrays, decoded objects,
`false`, or `WP_Error` depending on the helper and error path. The shared theme
helpers only treat `WP_Error` transport failures as request errors; non-2xx
Salesforce responses are decoded and returned to callers. Callers should not
assume a single success or error shape without checking the helper they use.
The donor-space helpers exit the current request when token acquisition returns
`WP_Error`, so browser-facing donor flows can fail harder than the theme helpers.

## Business Flows

### Newsletter

Newsletter forms check local signer data and Salesforce Contacts by email. They
create or update Contacts, set newsletter opt-in fields, create or update Leads
when only an email address is known, and delete existing Leads after a full
newsletter signup has become a Contact.

Newsletter origin mapping uses:

- `AIF_SALESFORCE_ORIGINE__C` for Contact `Origine__c`;
- `AIF_SALESFORCE_CODE_ORIGINE__C__WEB` for Lead `Code_Origine__c`.

### Petitions

When a petition is saved in the back office, the theme creates the corresponding
`Petition__c` record if the WordPress post does not already have a Salesforce
external ID. It stores the returned Salesforce record ID in `sfid`, the external
petition ID in `uidsf`, and the default origin code in `code_origine`.

Later end-date changes are PATCHed to `Petition__c` through the external ID.
Salesforce field limits are part of the contract. In particular, the petition
title sent as `Name` must respect the target Salesforce field length; do not
assume that the full WordPress title can always be sent unchanged.

### Petition Signatures

Public petition and support-action signatures are written to local tables first.
They are exported later to Salesforce as Bulk API rows for
`Signature_de_petition__c`. Each row includes the local petition ID, the
Salesforce petition external ID, signer details, signature type, origin code,
and optional support-action message.

### Urgent Actions

Urgent action registrations are synchronized from the local queue by WP-CLI.
The sync updates existing Salesforce Contacts or creates new Contacts, and may
create `Militant__c` records for militant registrations.

Origin and modification mapping uses:

- `AIF_SALESFORCE_CODES_AUWEBAPP` for SMS urgent actions;
- `AIF_SALESFORCE_CODES_AUWEB` for email urgent actions;
- `AIF_SALESFORCE_CODES_MILITANT` for militant registrations.

### Chronique

The Chronique request form creates Salesforce `Case` records with origin `Web`
and type `Offre Chronique`. Its `RecordTypeId` comes from
`AIF_SALESFORCE_RECORD_TYPE_ID`.

### Donor Space

`Mon Espace` uses the donor-space plugin helpers for authenticated donor and
member flows. The plugin calls both standard REST endpoints and custom Apex REST
endpoints:

- `services/apexrest/search/v1/{email}` for donor/member lookup;
- `services/apexrest/retrieve/v1/RecuFiscaux/` for tax receipts;
- `services/apexrest/retrieve/v1/Demandes/` for donor requests;
- `services/data/v57.0/sobjects/Contact/{id}` for Contact data and updates;
- `services/data/v57.0/sobjects/Contact/{id}/Mandats_SEPA__r` for SEPA
  mandates;
- `services/data/v57.0/sobjects/Case` for contact requests, tax receipt
  duplicate requests, and IBAN update requests.

Several donor-space `Case` payloads contain hardcoded `RecordTypeId` and
`Code_Marketing_Prestataire__c` values. Treat them as Salesforce business
contracts and confirm them with the Salesforce owner before changing them.

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
- The donor-space helpers may terminate the request on token errors.
- Some business flows depend on Salesforce during browser requests, especially
  newsletter signup, Chronique requests, login, and donor/member pages in
  `Mon Espace`.
- Petition signatures and urgent action registrations fail asynchronously during
  WP-CLI synchronization, not during the user-facing submit.
- Field names and record type IDs are business contracts; keep them explicit and
  reviewed with the business owner when they change.
- `.env.example`, the root README, and this document must stay aligned when
  Salesforce variables are added or removed.

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
