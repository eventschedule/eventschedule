#!/bin/bash
# The film's data, onto the film's private copy, the way the Product Hunt gallery's kit seeds its
# own: the app's demo, the kit's retheme into The Indigo Room, then the film's additions.
#
#   fixture/seed.sh COPY_DIR onsale|prescan|doors            from nothing (migrate:fresh, about a minute)
#   fixture/seed.sh COPY_DIR onsale|prescan|doors --film     only the film's part (switch state; seconds)
#
# The kit's seed scripts are COPIES in ~/.claude/plans/product-hunt-video/seed/ (retheme.php writes
# the owner's login beside itself as .login, which is why it must not be run from the kit's folder).
# Dates hang off the day it is run: Jazz Night is tonight at 8:00 PM, Eastern time.
set -euo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"
COPY="${1:?usage: seed.sh COPY_DIR onsale|prescan|doors [--film]}"
STATE="${2:?usage: seed.sh COPY_DIR onsale|prescan|doors [--film]}"
SEED="${LAUNCHFILM_KEEP:-$HOME/.claude/plans/product-hunt-video}/seed"

grep -q '^DB_DATABASE=eventschedule_test_launchfilm$' "$COPY/.env" || { echo "refusing: $COPY does not point at the film's schema" >&2; exit 1; }
[ -f "$HERE/poster/demo_launch_jazz_night.jpg" ] || { echo "no poster yet: run  node capture.mjs --poster" >&2; exit 1; }
mkdir -p "$COPY/public/images/demo" && cp "$HERE/poster/demo_launch_jazz_night.jpg" "$COPY/public/images/demo/"

if [ "${3:-}" != "--film" ]; then
  php "$COPY/artisan" migrate:fresh --force --no-interaction | tail -1
  php "$COPY/artisan" app:setup-demo --no-interaction | tail -1
  php "$SEED/run.php" "$COPY" "$SEED/retheme.php" | tail -1
fi
php "$SEED/run.php" "$COPY" "$HERE/film.php" "$STATE"
: > "$COPY/storage/logs/laravel.log"
