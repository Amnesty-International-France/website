#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "$0")" && pwd)"
readonly SCRIPT_DIR

# Jetpack-generated labels and messages must match the French assertions.
yarn env:e2e run cli wp language core install fr_FR --activate --quiet
yarn env:e2e run cli wp language plugin install jetpack fr_FR --quiet

yarn env:e2e run cli wp eval-file - < "${SCRIPT_DIR}/seed-wordpress.php"
yarn env:e2e run cli wp rewrite flush --quiet
