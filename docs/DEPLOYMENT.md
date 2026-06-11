# Deployment

The project deploys through GitHub Actions, then delegates most server-side work
to scripts that live on the remote Infomaniak hosts. Treat the workflow files as
the repository source of truth for when deployment runs, and the remote scripts
as the source of truth for what happens on each host.

## GitHub Actions Workflows

Deployment workflows live in `.github/workflows`.

- `deploy-release.yml` runs on pushes to `main`, uses the `RELEASE` GitHub
  environment, connects to the staging SSH host, runs `$HOME/deploy.sh` with the
  pushed ref name, then updates the schema from `$DOCUMENT_ROOT`.
- `deploy-prod.yaml` runs on pushes to `prod`, uses the `PROD` GitHub
  environment, connects to the production SSH host, runs `$HOME/deploy.sh`, then
  updates the schema from `$DOCUMENT_ROOT`.
- `deploy-fairness.yml` runs on pushes to `fairness-dev` and delegates the
  Clever Cloud deployment to `coopTilleuls/action-clevercloud-deploy`.

## Infomaniak Flow

Staging and production both rely on remote scripts outside this repository:

- `$HOME/deploy.sh`;
- `$HOME/initenv.sh`;
- the host crontab blocks read by `initenv.sh`.

The current workflow contract is:

```bash
ssh <host> '$HOME/deploy.sh [ref]'
ssh <host> 'source $HOME/initenv.sh && cd "$DOCUMENT_ROOT" && wp update-db-schema'
```

`deploy.sh` is state-changing. It may fetch Git refs, reset the remote checkout,
copy files into the WordPress document root, install frontend dependencies,
build frontend assets, and synchronize plugins and themes. Do not run it during
inspection.

## Remote Environment

`initenv.sh` is expected to expose at least:

- `REPO_DIR`: the remote Git checkout used by the deploy script;
- `DOCUMENT_ROOT`: the WordPress document root used by the deploy and schema
  update steps.

Production may also contain historical or cron-oriented variables such as
`DOCUMENT_ROOT_PROD`. Do not use those in deployment workflow changes unless the
production host has been checked directly and the workflow contract is updated
deliberately.

Never copy secret values from remote crontabs, shell profiles, or generated
environment files into documentation, commits, issues, or pull requests.

## Known Weak Points

- The main deployment scripts are remote files, not versioned in this
  repository.
- Staging and production can drift independently, so do not infer production
  behavior from staging.
- The schema update depends on the current WordPress root and on WP-CLI loading
  the theme command that registers `update-db-schema`.
- Remote deploy scripts are mutable operational assets; inspect them before
  changing GitHub Actions commands.

## Change Checklist

When changing deployment behavior:

- check the relevant GitHub Actions workflow;
- inspect the target remote scripts without running deployment;
- verify which variables `initenv.sh` exposes on that host;
- avoid executing `$HOME/deploy.sh` while investigating;
- avoid printing remote environment secrets;
- validate workflow YAML with `actionlint`;
- verify `wp-load.php` and WP-CLI command registration in the intended WordPress
  root before changing schema-update commands;
- after merge, check the corresponding GitHub Actions run.
