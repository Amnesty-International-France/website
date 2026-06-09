# Cloudflare Turnstile Integration

Cloudflare Turnstile protects sensitive public and authenticated forms. The
project integrates it directly in `humanity-theme`; it does not rely on the
Cloudflare WordPress plugin for form protection.

## Runtime Flow

The theme exposes three central helpers in `functions.php`:

- `aif_turnstile_site_key()`;
- `aif_turnstile_secret_key()`;
- `verify_turnstile()`.

When `TURNSTILE_SITE_KEY` is configured, the theme enqueues:

- `assets/scripts/turnstile.js`;
- `https://challenges.cloudflare.com/turnstile/v0/api.js`.

The local script is loaded first so submit guards and global callbacks exist
before the Cloudflare script renders widgets.

Protected forms render `.cf-turnstile` widgets with the configured site key and
the project callbacks:

- `aifTurnstileSuccess`;
- `aifTurnstileFailure`.

On submit, PHP verifies the `cf-turnstile-response` token against Cloudflare's
`siteverify` endpoint and maps known error codes to French user-facing
messages.

Cloudflare tokens are server-validated, single-use, and expire after five
minutes. A reused or expired token is surfaced by Cloudflare as
`timeout-or-duplicate`, which this project maps to a reload-and-retry message.

Current protected forms include login, account creation, email verification,
password reset/change, newsletter forms, petition signatures, and urgent action
registration.

## Environment

Required runtime variables:

```text
TURNSTILE_SITE_KEY
TURNSTILE_SECRET_KEY
```

The theme reads them with `getenv()` only. Local tooling may inject them into
the PHP process, but application code should not branch on constants or
test-specific hooks.

If `TURNSTILE_SITE_KEY` is empty, the Turnstile scripts are not enqueued. If the
site key is present but the secret is missing or invalid, widgets can render but
server-side validation will fail.

## Local Development

`private/.wp-env.json` mounts the local-only plugin
`private/wp-env/dev-turnstile`. That plugin injects Cloudflare dummy keys when
the local `wp-env` stack starts and the WordPress environment type is `local` or
`development`.

```bash
cd private
yarn env start --update
```

The local stack is then available at `http://localhost:8888`. A protected page
such as `/mot-de-passe-oublie/` should render a real Cloudflare dummy widget in
the browser.

Default local dummy keys:

```text
TURNSTILE_SITE_KEY=1x00000000000000000000BB
TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA
```

Cloudflare documents this site key as an invisible widget that always passes
and this secret key as always passing server-side validation. Production secrets
reject dummy tokens, so local dummy widgets must be paired with dummy secrets.

## Automated Tests

Playwright E2E tests live under
[`private/tests/e2e`](../private/tests/e2e/README.md).

Deterministic E2E tests mock the Cloudflare script and server verification
through Playwright routes and the local E2E WordPress support plugin.
The real Cloudflare dummy-key smoke test is intentionally opt-in:

```bash
cd private
RUN_CLOUDFLARE_SMOKE=1 yarn test:e2e tests/e2e/turnstile-cloudflare-dummy.spec.mjs
```

This keeps the default suite independent from Cloudflare network availability
while still allowing a real widget check before release.

## Change Conventions

When adding Turnstile to a form:

- render a `.cf-turnstile` container only when the form needs protection;
- include the standard callback attributes;
- call `verify_turnstile()` before executing the sensitive action;
- surface `turnstile_friendly_error()` when verification fails;
- keep frontend guard changes in the dedicated `turnstile` Webpack entry;
- update or add E2E coverage for the protected business flow.

Recommended checks:

```bash
cd private
yarn test
yarn typecheck:turnstile
yarn build:prod --env entry=turnstile
yarn test:e2e tests/e2e/turnstile-guard.spec.mjs tests/e2e/turnstile-forgot-password-flow.spec.mjs --project=chromium
```
