#!/bin/bash
# The launch film's private, served copy of the app, made the way the Product Hunt gallery's kit
# makes its own (~/.claude/plans/product-hunt/make-copy.sh), so film and gallery show one venue.
#
#   fixture/make-copy.sh COPY_DIR [PORT]
#
# It clones the working tree as it stands (APFS clones: no disk used), writes an .env from
# .env.example (no service keys; only the local MySQL login is read from the repo's .env, and never
# printed), and drops, creates and migrates its OWN schema. It never touches the dev database, the
# repo's .env, the Herd site, the kit's copy or the kit's schema (eventschedule_test_ph).
#
# Afterwards (see fixture/seed.sh, which does all of it):
#   the one staged edit, npm run build, app:setup-demo, the kit's seeds, film.php, serve.
set -euo pipefail
REPO=/Users/hillel/Code/eventschedule
COPY="${1:?usage: make-copy.sh COPY_DIR [PORT]}"
PORT="${2:-8091}"
DB=eventschedule_test_launchfilm

case "$COPY" in *launchfilm-app) ;; *) echo "refusing: COPY_DIR must end in launchfilm-app" >&2; exit 1;; esac
case "$PORT" in 8000|9515|8073) echo "refusing port $PORT (Dusk, ChromeDriver, another session)" >&2; exit 1;; esac
if lsof -nP -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; then echo "port $PORT is taken: pass another" >&2; exit 1; fi

rm -rf "$COPY"
mkdir -p "$COPY"
for d in app bootstrap config database public resources routes tools vendor node_modules; do cp -Rc "$REPO/$d" "$COPY/$d"; done
for f in artisan composer.json composer.lock package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js; do cp -c "$REPO/$f" "$COPY/$f"; done
mkdir -p "$COPY"/storage/{app/public,framework/cache/data,framework/sessions,framework/views,framework/testing,logs}
cp -c "$REPO"/storage/*.json "$COPY/storage/"
rm -f "$COPY"/bootstrap/cache/*.php "$COPY/public/hot"
# The film's own folder rode along inside resources/: it is not part of the app, and it is big.
rm -rf "$COPY/resources/promo"
git -C "$REPO" rev-parse HEAD > "$COPY/.made-from"
git -C "$REPO" status --short | wc -l | tr -d ' ' >> "$COPY/.made-from"

python3 - "$REPO" "$COPY" "$PORT" "$DB" <<'PY'
import base64, os, re, sys
repo, app, port, db = sys.argv[1:5]
# The local MySQL login, and nothing else, from the repo's .env. Never printed.
login = dict(l.split('=', 1) for l in open(f'{repo}/.env').read().splitlines() if re.match(r'DB_(HOST|PORT|USERNAME|PASSWORD)=', l))
want = {
    'APP_NAME': '"Event Schedule"', 'APP_ENV': 'local', 'APP_DEBUG': 'false', 'GEMINI_API_KEY': 'placeholder-not-a-key', 'APP_URL': f'http://127.0.0.1:{port}',
    'APP_KEY': 'base64:' + base64.b64encode(os.urandom(32)).decode(), 'APP_TIMEZONE': 'UTC', 'APP_LOCALE': 'en',
    'APP_TESTING': 'true', 'IS_HOSTED': 'true', 'IS_NEXUS': 'true', 'REPORT_ERRORS': 'false',
    'DB_CONNECTION': 'mysql', 'DB_HOST': login.get('DB_HOST', '127.0.0.1'), 'DB_PORT': login.get('DB_PORT', '3306'),
    'DB_DATABASE': db, 'DB_USERNAME': login['DB_USERNAME'], 'DB_PASSWORD': login['DB_PASSWORD'],
    'SESSION_DRIVER': 'file', 'CACHE_STORE': 'file', 'QUEUE_CONNECTION': 'sync', 'MAIL_MAILER': 'array',
    'LOG_CHANNEL': 'single', 'FILESYSTEM_DISK': 'local', 'COOKIE_CONSENT_BANNER': 'false', 'ADS_ENABLED': 'false',
    'SESSION_SECURE_COOKIE': 'false',
}
out, seen = [], set()
for line in open(f'{repo}/.env.example').read().splitlines():
    m = re.match(r'\s*([A-Z0-9_]+)=', line)
    if not m: out.append(line); continue
    k = m.group(1); seen.add(k)
    out.append(f'{k}={want[k]}' if k in want else line)   # otherwise the example's own value: the committed, keyless default
out += [f'{k}={v}' for k, v in want.items() if k not in seen]
open(f'{app}/.env', 'w').write('\n'.join(out) + '\n')
print('env written,', len(out), 'lines')
PY

# Its own schema, and only its own: the name is checked against the .env before anything is dropped.
cd "$COPY"
php -r '
$e = []; foreach (file(".env") as $l) { if (strpos($l, "=") !== false && $l[0] !== "#") { [$k, $v] = explode("=", rtrim($l, "\n"), 2); $e[$k] = trim($v, "\""); } }
if ($e["DB_DATABASE"] !== "eventschedule_test_launchfilm") { fwrite(STDERR, "refusing: the copy points at {$e["DB_DATABASE"]}\n"); exit(1); }
$pdo = new PDO("mysql:host={$e["DB_HOST"]};port={$e["DB_PORT"]}", $e["DB_USERNAME"], $e["DB_PASSWORD"]);
$pdo->exec("DROP DATABASE IF EXISTS `eventschedule_test_launchfilm`");
$pdo->exec("CREATE DATABASE `eventschedule_test_launchfilm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "schema ", $e["DB_DATABASE"], " created\n";'
php artisan migrate --force --no-interaction 2>&1 | tail -2
echo "copy ready at $COPY on port $PORT (made from $(head -1 .made-from | cut -c1-9), $(tail -1 .made-from) files uncommitted)"
