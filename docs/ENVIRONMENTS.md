# Environments

The project runs in several local, test, staging, production, and historical
hosting contexts. This document maps those environments and where their
configuration comes from. Deployment operations live in
[`DEPLOYMENT.md`](./DEPLOYMENT.md).

## Environment Matrix

| Environment | Purpose | Runtime | Configuration Source | Main Docs |
| --- | --- | --- | --- | --- |
| Local Castor/WP-CLI | Historical manual local development | Local PHP, WP-CLI, MySQL or MariaDB | Root `.env`, based on `.env.example` | [`README.md`](../README.md) |
| Local `wp-env` | Docker-based local WordPress development | `@wordpress/env` on `http://localhost:8888` | `private/.wp-env.json` and local support plugins | [`private/README.md`](../private/README.md) |
| E2E `wp-env` | Playwright end-to-end tests | `@wordpress/env` on `http://localhost:8898` | `private/tests/e2e/.wp-env.e2e.json` and E2E support plugin | [`private/tests/e2e/README.md`](../private/tests/e2e/README.md) |
| Release / preprod | Remote validation environment for `main` | Infomaniak WordPress host | GitHub `RELEASE` environment, remote `~/initenv.sh`, host crontab | [`DEPLOYMENT.md`](./DEPLOYMENT.md) |
| Production | Public production site for `prod` | Infomaniak WordPress host | GitHub `PROD` environment, remote `~/initenv.sh`, host crontab | [`DEPLOYMENT.md`](./DEPLOYMENT.md) |
| Fairness / Clever Cloud | Historical or dedicated Clever Cloud deployment path | Clever Cloud PHP runtime | Clever Cloud environment variables and `infogerance/aif-clever-cloud.php` | [`DEPLOYMENT.md`](./DEPLOYMENT.md), [`ARCHITECTURE.md`](./ARCHITECTURE.md) |

## Variable Ownership

Use the narrowest source of truth for each variable family:

- `.env.example`: baseline variables expected by the historical local
  Castor/WP-CLI setup, including database, WordPress admin, Salesforce, and
  Mailgun values.
- `private/.wp-env.json`: local Docker WordPress mounts, local-only plugins, and
  lifecycle activation for the default `wp-env` stack.
- `private/tests/e2e/.wp-env.e2e.json`: isolated E2E WordPress runtime, port, and
  local test support plugin.
- GitHub environments and secrets: SSH credentials and environment selection for
  deployment workflows.
- Remote `~/initenv.sh` and host crontab: Infomaniak deployment variables such as
  `REPO_DIR` and `DOCUMENT_ROOT`.
- `infogerance/aif-clever-cloud.php`: Clever Cloud mapping from platform
  environment variables to WordPress configuration.

Integration-specific variables are documented with their subsystem:

- [`SALESFORCE.md`](./SALESFORCE.md): Salesforce OAuth and business-code
  variables.
- [`TURNSTILE.md`](./TURNSTILE.md): Cloudflare Turnstile site and secret keys,
  including local dummy-key behavior.
- [`MON-ESPACE.md`](./MON-ESPACE.md): authenticated user-area behavior and
  integration boundaries.

## Related Operations

- To change GitHub Actions deployment commands, remote scripts, or schema-update
  checks, start with [`DEPLOYMENT.md`](./DEPLOYMENT.md).
- To run the default local Docker WordPress stack or rebuild frontend assets, use
  [`private/README.md`](../private/README.md).
- To run Playwright against the isolated E2E WordPress stack, use
  [`private/tests/e2e/README.md`](../private/tests/e2e/README.md).
- To add or change integration variables, update `.env.example` and the relevant
  integration document.

## Weak Points

- Remote Infomaniak variables are owned by host-side shell scripts and crontabs,
  not by this repository.
- Local `wp-env` and E2E `wp-env` are deterministic but do not model all
  production integrations.
- Clever Cloud support is historical and follows a different configuration model
  from Infomaniak.
- Several integrations read directly from `getenv()`, so new required variables
  should update `.env.example` and the relevant integration documentation.
