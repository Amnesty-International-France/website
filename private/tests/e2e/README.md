# Playwright E2E Tests

This directory contains the Playwright end-to-end setup for the frontend
behaviors that need a running WordPress site. The current suite focuses on the
Cloudflare Turnstile integration introduced for protected forms.

## Layout

- `.wp-env.e2e.json`: dedicated `wp-env` configuration for E2E tests.
- `support/seed-wordpress.sh`: seeds the minimal WordPress content required by
  the tests.
- `support/turnstile.mjs`: deterministic Playwright helpers and Cloudflare
  Turnstile mocks.
- `wordpress/aif-e2e-support/`: local-only WordPress plugin for the E2E
  environment. It provides Turnstile dummy keys and small stubs for dependencies
  that are not relevant to these tests.
- `turnstile-guard.spec.mjs`: deterministic regression tests for the local
  Turnstile guard.
- `turnstile-cloudflare-dummy.spec.mjs`: opt-in smoke tests using Cloudflare's
  official dummy Turnstile keys.

## Installation

Run commands from `private/`.

```bash
yarn install --immutable
yarn playwright install chromium
```

On Linux CI runners or fresh machines that miss browser system dependencies,
use:

```bash
yarn playwright install --with-deps chromium
```

## WordPress E2E Environment

The E2E environment is isolated from the default local `wp-env` setup:

- config file: `tests/e2e/.wp-env.e2e.json`;
- site URL: `http://localhost:8898`;
- tests environment: disabled with `testsEnvironment: false`;
- support plugin: `tests/e2e/wordpress/aif-e2e-support`;
- theme: `../wp-content/themes/humanity-theme`.

Start, seed, and stop it with:

```bash
yarn env:e2e:start
./tests/e2e/support/seed-wordpress.sh
yarn env:e2e:stop
```

If the environment must be rebuilt from scratch, use `--force` to avoid the
interactive prompt:

```bash
yarn env:e2e:destroy --force
```

## Running Tests

Run the PR-blocking deterministic suite:

```bash
yarn test:e2e --grep-invert @cloudflare-smoke
```

Run all Playwright tests. Without `RUN_CLOUDFLARE_SMOKE=1`, the Cloudflare smoke
tests are listed but skipped:

```bash
yarn test:e2e
```

Run the optional Cloudflare dummy-key smoke tests:

```bash
RUN_CLOUDFLARE_SMOKE=1 yarn test:e2e tests/e2e/turnstile-cloudflare-dummy.spec.mjs --project=chromium
```

Open the HTML report after a run:

```bash
yarn test:e2e:report
```

## Turnstile Test Strategy

The suite has two layers.

Deterministic tests mock Cloudflare's `api.js` before page navigation. They
verify the behavior owned by this project:

- the local Turnstile guard is loaded before Cloudflare's script;
- widgets render with the configured dummy site key;
- early form submission waits for a Turnstile token;
- delayed resubmission preserves form data and named submitters;
- client-side Turnstile failures do not submit the form;
- missing tokens surface the expected user-facing error.

The Cloudflare smoke tests use the official dummy keys documented by
Cloudflare. They verify that the real Cloudflare script can create a token and
that server-side validation accepts and rejects dummy tokens as expected.

Keep deterministic tests as the required PR signal. Keep real Cloudflare smoke
tests opt-in unless they prove stable enough for every PR.

## CI

The CI frontend job runs from `private/`:

```bash
yarn install --immutable
yarn build:prod --env entry=turnstile
yarn test
yarn typecheck:turnstile
yarn playwright install --with-deps chromium
yarn env:e2e:start
./tests/e2e/support/seed-wordpress.sh
yarn test:e2e --grep-invert @cloudflare-smoke
yarn env:e2e:stop
```

The stop step must stay guarded with `if: always()` in GitHub Actions so Docker
containers are stopped even when tests fail.

## Maintenance Notes

- Do not commit real Turnstile keys. Use only Cloudflare dummy keys in this
  directory.
- Keep local E2E-only WordPress stubs inside
  `wordpress/aif-e2e-support/`; do not add them to production code.
- Keep paths in `.wp-env.e2e.json` relative to the `private/` working
  directory, because `wp-env` resolves local sources from the command cwd.
- Prefer route-based Cloudflare mocks for regression tests. Use real Cloudflare
  only for the small opt-in smoke suite.
