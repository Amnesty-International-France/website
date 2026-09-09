#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "$0")" && pwd)"
readonly SCRIPT_DIR

# `yarn env:e2e` resolves its --config path relative to the CWD, so the whole
# script only works from `private/`.
cd "${SCRIPT_DIR}/../../.."

# Jetpack-generated labels and messages must match the French assertions.
yarn env:e2e run cli wp language core install fr_FR --activate --quiet
yarn env:e2e run cli wp language plugin install jetpack fr_FR --quiet

yarn env:e2e run cli wp eval-file - < "${SCRIPT_DIR}/seed-wordpress.php"

# `eval-file -` feeds the seed through the host -> container stdin of
# `docker compose run`. If that forwarding fails on another runner, eval-file
# gets an empty input and seeds nothing without erroring. Assert the first and
# last seeded pages exist, so an empty or half-run seed fails here instead of
# turning into opaque Playwright timeouts.
seed_check='echo get_page_by_path("accueil-e2e") && get_page_by_path("formulaire-foundation") ? "AIF_SEED_OK" : "AIF_SEED_INCOMPLETE";'
if ! yarn env:e2e run cli wp eval "${seed_check}" | grep -q AIF_SEED_OK; then
    echo "seed-wordpress: seed did not complete, expected pages are missing." >&2
    exit 1
fi

yarn env:e2e run cli wp rewrite flush --quiet
