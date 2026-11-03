#!/usr/bin/env bash
# Regenerates the free Stroke Rounded set bundled with this package from the
# @hugeicons/core-free-icons version pinned in package.json.
#
#   composer icons:bundle
#
# The bundled directory is wiped first so icons removed upstream do not linger.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
target="$root/resources/views/flux/icon/hugeicons"

cd "$root"
npm install --no-audit --no-fund --ignore-scripts

rm -rf "$target"
vendor/bin/testbench hugeicons:build \
    --node-modules="$root/node_modules" \
    --target="$target" \
    --styles=stroke-rounded \
    --force

echo "Bundled $(find "$target" -name '*.blade.php' | wc -l | tr -d ' ') icons from @hugeicons/core-free-icons $(node -p "require('./node_modules/@hugeicons/core-free-icons/package.json').version")."
