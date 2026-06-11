# Petitions And Urgent Actions

Petitions and urgent actions let visitors sign campaigns, register for urgent
action updates, and queue those interactions for Salesforce synchronization.

## Role

The theme owns the petition and urgent action flows today. This is not only
presentation code: the theme also stores business data locally and synchronizes
it with Salesforce.

The subsystem covers:

- the public `petition` post type and `/petitions` archive;
- public petition and support-action forms;
- urgent action registration forms;
- local signer/contact storage;
- local queues for petition signatures and urgent action registrations;
- deferred Salesforce synchronization;
- public and `Mon Espace` petition rendering.

## Main Flows

### Petition Signature

Public petition pages render a signing form protected by Cloudflare Turnstile.
On submit, the theme validates the request, rejects expired petitions, finds or
creates a local signer, prevents duplicate signatures for the same signer and
petition, stores the local signature, increments the local counter, then
redirects to the thank-you route.

Signatures are not sent to Salesforce during the browser request. They are
queued locally and synchronized later through WP-CLI.

### Support Action

Support actions reuse the `petition` post type and the petition signature
pipeline. Their form may collect an additional user message that is stored with
the local signature and later exported to Salesforce.

### Urgent Action Registration

Urgent action forms are rendered by the dynamic
`amnesty-core/urgent-register-form` block. On submit, the theme validates
Turnstile, checks the selected action type, finds or creates a local signer,
deduplicates by signer and action type, then stores an unsynchronized urgent
action registration.

Urgent action registrations are also synchronized later through WP-CLI.

### Salesforce Petition Creation

When a petition is saved in the back office, the theme creates the corresponding
Salesforce petition if it does not already have a Salesforce external ID. Later
end-date changes are PATCHed to Salesforce.

## Data Ownership

The subsystem uses three project-owned tables:

- `wp_aif_users`: local signer/contact identity shared by petitions and urgent
  actions;
- `wp_aif_petitions_signatures`: local petition/support-action signatures and
  their Salesforce sync status;
- `wp_aif_urgent_action`: urgent action registrations waiting for Salesforce
  sync.

These tables are business data. Treat schema changes as data migrations, not as
theme-only rendering changes.

Petition tables are created on `after_switch_theme`. Urgent action tables are
also created on `after_switch_theme` and have a WP-CLI schema update path.

## External Integrations

The subsystem depends on:

- Salesforce REST API and Bulk API for petition creation, signature export,
  user updates, and urgent action subscriptions;
- Cloudflare Turnstile for public form validation;
- ACF for petition metadata and back-office editing;
- WP-CLI for asynchronous synchronization;
- the local `fiche_pays` content type for country choices in forms.

Salesforce credentials and mapping values are read through environment
variables documented in the Salesforce and environment documentation.

## Operational Commands

Petition synchronization commands are exposed under `wp sync`:

```bash
wp sync import_users
wp sync import_signatures
wp sync compteurs
wp sync signatures
wp sync signatures_failed
```

Urgent action synchronization is also exposed under `wp sync`:

```bash
wp sync syncs_ua_with_salesforce
```

Urgent action schema updates use:

```bash
wp update-db-schema
```

Flush rewrite rules after changing petition routes, the thank-you route, or
`Mon Espace` petition routing.

## Known Weak Points

- The theme owns business persistence and Salesforce synchronization, so the
  boundary between presentation and application logic is blurred.
- Petition table schema changes do not currently have the same explicit
  versioned update path as urgent action schema changes.
- Petition and urgent action modules both register a `sync` WP-CLI command;
  verify the effective command registration before relying on both command
  sets.
- The public `humanity/v1/check-email` endpoint reveals whether an email exists
  in the local signer table.
- The petition admin type filter has a known typo around the `action-soutien`
  selected value.
- Some petition form fields are read from `$_POST`; review sanitation and
  validation when changing the form.
- The browser request only writes local queue rows. Salesforce failures appear
  later during CLI synchronization, not during the user-facing submit.

## Code Map

Start here when changing this subsystem:

- `includes/post-types/petitions.php`: petition CPT, signature submit handler,
  archive filtering, ACF fields, and `Mon Espace` petition route.
- `includes/petitions/tables.php`: local signer and signature persistence.
- `includes/petitions/create-petition.php`: Salesforce petition create/update
  hooks.
- `includes/petitions/syncs.php`: petition WP-CLI synchronization commands.
- `includes/urgent-action/tables.php`: urgent action persistence and schema
  command.
- `includes/urgent-action/syncs.php`: urgent action Salesforce sync command.
- `includes/salesforce/petition.php`: Salesforce petition and Bulk API helpers.
- `patterns/aside-petition-sticky.php`: public petition/support-action form.
- `partials/urgent-register-form.php`: urgent action registration form.
- `templates/single-petition.html`, `templates/archive-petition.html`, and
  `patterns/single-petition-my-space.php`: public and authenticated rendering.

## Change Checklist

Before shipping changes in this area:

- test a public petition signature with Turnstile enabled or dummy keys;
- test duplicate signature behavior;
- test an urgent action registration for each changed action type;
- run or dry-run the relevant WP-CLI sync path in an environment with safe
  Salesforce credentials;
- verify schema changes have a migration/update path;
- check public and `Mon Espace` rendering for petition pages.
