# Environments And Deployment

This document summarizes the project environment model and the main deployment
paths. It complements the root README and subsystem documentation.

## Local WordPress

The historical local installation path uses the root `.env` file, Castor,
WP-CLI, and a local MySQL or MariaDB database.

The required baseline variables are listed in `.env.example`:

- database connection;
- WordPress URL, title, and admin account;
- `WP_ENVIRONMENT_TYPE`;
- Salesforce credentials;
- Mailgun credentials.

Local `.env` values must be loaded by the developer WordPress configuration,
for example from `wp-config.php` as shown in the root README. Runtime
application code mostly reads configuration through `getenv()`, with a few
subsystems also accepting constants.

## Frontend Local Stack

The frontend toolchain lives in `private/`.

Important commands:

```bash
cd private
yarn install --immutable
yarn build
yarn env start --update
```

`private/.wp-env.json` mounts the main theme and required CMB2 plugins into a
local WordPress container. It also mounts `private/wp-env/dev-turnstile` so the
local stack can display Turnstile widgets with Cloudflare dummy keys.

`@wordpress/env` uses `http://localhost:8888` by default for the development
site. This project does not override that port in `private/.wp-env.json`.

The E2E stack is separate and uses `private/tests/e2e/.wp-env.e2e.json` on port
`8898`, with `testsEnvironment` disabled. See
[`private/tests/e2e/README.md`](../private/tests/e2e/README.md).

## Clever Cloud

Historical Clever Cloud support is kept under:

- `clevercloud/pre_build.sh`;
- `clevercloud/post_build.sh`;
- `clevercloud/cron.json`;
- `infogerance/aif-clever-cloud.php`.

`infogerance/aif-clever-cloud.php` generates a WordPress configuration from
platform environment variables. It maps Clever Cloud MySQL variables to
WordPress constants and enables Jetpack development or staging behavior based on
`WP_ENVIRONMENT_TYPE`.

## GitHub Actions Deployment

Deployment workflows live in `.github/workflows`.

Current deployment paths:

- `deploy-release.yml`: runs on pushes to `main`, uses the `RELEASE`
  environment, connects to the staging SSH host, executes `$HOME/deploy.sh`,
  then runs `wp update-db-schema` from
  `$DOCUMENT_ROOT`;
- `deploy-prod.yaml`: runs on pushes to `prod`, uses the `PROD` environment,
  connects to the production SSH host, executes `livraison-prod.sh`, then runs
  `wp --path="$DOCUMENT_ROOT_PROD" update-db-schema`;
- `deploy-fairness.yml`: runs on pushes to `fairness-dev` and delegates the
  Clever Cloud deployment to `coopTilleuls/action-clevercloud-deploy`.

Staging and production do not expose the same shell variables. Do not assume
that a deployment command validated on staging can be copied to production
without checking the remote shell profile and document root variables.

## Remote Operational Context

Known staging context:

- SSH alias: `amnesty-preprod`;
- `~/initenv.sh` exposes `REPO_DIR` and `DOCUMENT_ROOT`;
- observed WP-CLI version: `2.12.0`.

Useful non-mutating check:

```bash
ssh amnesty-preprod 'source $HOME/initenv.sh && printf "DOCUMENT_ROOT=%s\n" "$DOCUMENT_ROOT" && test -d "$DOCUMENT_ROOT" && cd "$DOCUMENT_ROOT" && wp --version'
```

## Change Conventions

When changing environment or deployment behavior:

- update `.env.example` and docs when a new required variable is introduced;
- keep local `wp-env`, E2E `wp-env`, staging, and production assumptions
  separate;
- validate deployment shell changes with a non-mutating remote command before
  running a state-changing command;
- check `wp update-db-schema` paths independently for staging and production.
