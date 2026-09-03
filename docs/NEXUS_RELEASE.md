# Nexus release runbook

How a release reaches the hosted install (eventschedule.com), and the ordered cutover for the
two pieces of infrastructure that ship with **v1.0.129**: edge caching of marketing HTML, and
moving the scheduler onto a DigitalOcean App Platform worker.

Selfhosted installs are unaffected by everything here. Cutting the GitHub release that
selfhosters update from is a **separate, later act** - see [Selfhost release](#selfhost-release)
at the end.

Steps marked **[one-time]** are the v1.0.129 infrastructure cutover and will not recur. The rest
is the standing shape of a hosted deploy.

**This file is self-contained for the cutover.** Everything you need to do on the day, verify
after it, and reach for when something goes wrong is here - you should not have to open another
document mid-deploy. [`CACHING.md`](CACHING.md) and
[`DIGITALOCEAN_WORKER.md`](DIGITALOCEAN_WORKER.md) go deeper on *why* each mechanism works the
way it does: the origin cache contract the middleware implements, what moved into the browser,
and the full worker reference. Read them before changing any of it; you do not need them to run
the cutover.

## What is shipping

`main` is 47 commits and 11 migrations ahead of what is live. Deploys are manual
(`deploy_on_push` is unset on the app spec), so nothing in this release has reached production.

Two commands do most of the checking in this file, and both are read-only:

```bash
php artisan deploy:preflight     # before you deploy: working tree, live app spec, baseline
php artisan deploy:verify        # after each step: edge headers, scheduler, queue, cache
```

`deploy:preflight` runs from a checkout (it reads the live app spec over the DO API).
`deploy:verify` runs from anywhere for the edge half and on the container for the rest; before
the cutover it is SUPPOSED to fail its edge assertions, which is how you know they are real.

The pre-deploy baseline, confirmed live:

```
curl -sI https://eventschedule.com/pricing
  cache-control: no-cache, private
  set-cookie: laravel_session=...; domain=.eventschedule.com
  cf-cache-status: BYPASS
```

Two changes need operator action **outside the repo** before they do anything at all:

1. **Edge caching.** Anonymous marketing GETs now come back cookie-free with
   `public, max-age=0, s-maxage=600`, and page-view counting plus first-touch attribution moved
   into the browser (a `sendBeacon` and the `es_attribution` cookie). The origin half ships with
   the deploy; the **bypass-on-cookie rule is a Cloudflare dashboard change** and no response
   header can substitute for it. Until it exists the code is inert. If it is wrong, a signed-in
   visitor can be served the anonymous copy for up to 10 minutes.
2. **The scheduler worker.** Every scheduled task currently runs inside `GET /translate_data`,
   where each tier claims its cache key *before* doing the work, so a request killed by the FPM
   timeout silently forfeits the rest of that tier. This release adds the health instrumentation,
   the named and bounded overlap mutexes and the runbook; the worker component itself has to be
   created in the DigitalOcean console.

## Pre-flight

Read-only. Do all of it before touching anything. It is worth the ten minutes because most of
what it catches is invisible from the outside: an unpushed commit or an untracked asset (the
deploy ships `origin/main`, not your disk), a variable that is set on the app spec but blank, and
a subscriber on a Stripe price ID this release no longer recognises.

| # | Check | Why it matters |
|---|---|---|
| P1 | **Snapshot the database.** | `2026_08_28_000000_replace_federated_event_url_with_is_online.php` **drops `federated_events.event_url`**. Its own `down()` recreates the column empty: the stored links are not recoverable, which is the point of the change. `2026_09_02_000000_reset_blog_post_updated_at.php` rewrites `updated_at` on ~161 blog rows with a no-op `down()`. Neither is reversible by a deployment rollback. |
| P2 | `deploy:preflight` pointed at production (see below) | **Three** migrations land on `events`, not two. Two are ALTERs: `widen_events_event_url` (varchar 255 to 500, a table rebuild on MySQL 8) and `add_image_variants_to_events` (a JSON column at the end, so INSTANT). The third, `reset_untouched_coupon_discount_types`, is the only one that WRITES rows, and neither column it filters on is indexed - so it scans `events` inside the start command's `migrate --force`. Its write set is small (the columns only exist since 2026-08-21) but the scan is not. `federated_events` is separately rebuilt **twice** in one migration: `replace_federated_event_url_with_is_online` adds `is_online` positionally with `->after()`, which forfeits `ALGORITHM=INSTANT`, then drops `event_url`. On large tables, run those three migrations by hand from the console *before* triggering the deploy. |
| P3 | `php artisan deploy:preflight` from your clone | Production config is the app spec, not any `.env`. The command confirms `QUEUE_CONNECTION=database`, `APP_URL`, `IS_HOSTED`, `IS_NEXUS`, reports `CACHE_STORE`, checks the web service is **`instance_count: 1`** (more than one container on the `file` cache store means every lock in the app serialises against nothing), prints the deployment ID a rollback targets, and writes the full custom-domain list to `storage/deploy/`. |
| P4 | `deploy:preflight` pointed at production, or on the container after step 2 | **The highest-value check in this table, and the one that was missing.** The legacy price recognition mechanism was removed this release, so `PlanPriceUtils` now matches a tier *only* against the four `STRIPE_PRICE_*` IDs on the spec. The command compares every live subscription's price ID against those four using `PlanPriceUtils` itself, so the check cannot drift from what the app believes. Anything it flags is a customer whose card is still being charged while `hasActiveEnterpriseSubscription()` returns false, both webhook handlers decline to write and ARR counts them at zero - the cost is spelled out in `PlanPriceUtils::tierFor()`'s docblock. Note the four configured IDs all share a `price_1T3s...` prefix, i.e. one creation batch, so anyone predating it is already stranded. `deploy:preflight` also confirms no `STRIPE_LEGACY_*` remains on the spec. |
| P5 | `deploy:preflight` on the container after step 2 | The *defaults* changed from 9/90/29/290 to 5/50/15/150. **The env vars are named `STRIPE_PRICE_MONTHLY_AMOUNT`, `STRIPE_PRICE_YEARLY_AMOUNT`, `STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT` and `STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT`** - earlier revisions of this file named `STRIPE_PRO_MONTHLY_AMOUNT`, which exists nowhere in the codebase. But config is only the *second* layer: `PlatformPricing` reads the `settings` row first, so what the site advertises is decided by that row, not by the spec. The command prints both layers side by side. As of writing production already advertises 5/50/15/150, so the config change is an alignment and the displayed price does not move. Note ARR, MRR and renewal emails deliberately read **config**, never `PlatformPricing` - so those figures *will* restate on deploy. That is a reporting artefact, not lost revenue. |
| P6 | `php artisan app:export-growth --days=30` on the container (exists on the current build) | The CLI twin of the `/admin/users` funnel, so no browser session is needed. It prints JSON to stdout - copy it somewhere off the container rather than using `--path`, because the container disk is ephemeral and step 2's deploy wipes it, which is precisely when you need the before-number. Record the "Visited site", page-view, docs and pricing funnel numbers. After the Cloudflare rule, origin-side counting stops and the beacon takes over; without a before-number a broken beacon is indistinguishable from normal variance. |
| P7 | Confirm the external cron can be disabled in one click, and record the current `APP_CRON_SECRET` | Re-enabling the cron is the only emergency fallback that does not require fixing the worker. Step 8 says **disable, not delete**. |
| P8 | `php artisan deploy:preflight`, then a green CI run | The deploy ships `origin/main`, not your disk. `deploy:preflight` fails on an unpushed commit or a dirty tree, and lists untracked paths separately because those are the ones `git commit -am` silently leaves behind - an asset referenced by committed code deploys as a 404 and nothing local ever notices. It also fails if `config/sitemap_lastmod.php` is older than the newest marketing view, which is the "run `php artisan sitemap:lastmod` before a release" rule made mechanical. `.github/workflows/test.yml` runs the whole Unit and Feature suite on push. There is **no release gate** on `build.yml`. |

### Where each half of `deploy:preflight` runs

The command does two things, and they answer to different machines.

**From your clone**, and this is the half that gates the deploy: the working tree, the live app
spec over the DO API, and the production header baseline. That is P3 and P8.

```bash
php artisan deploy:preflight
```

It also runs the three database checks, but against whatever your `.env` points at - your dev
data. It prints the schema and host it connected to on every run, so read that line and ignore
those three here.

**The database checks need the production database, and there is a catch worth stating plainly:
`deploy:preflight` ships IN this release, so it does not exist on the container until step 2 has
deployed.** Two ways round it, and the second is the honest default:

1. *Before the deploy*, point the command at production for one run. Credentials are in the
   DigitalOcean database console, under the cluster's connection details:

   ```bash
   DB_HOST=... DB_PORT=25060 DB_DATABASE=defaultdb DB_USERNAME=... DB_PASSWORD=... \
     php artisan deploy:preflight
   ```

   Worth doing for P4 specifically. A stranded subscriber is not created by this deploy - the
   legacy mechanism was already inert on production - but it is the one finding that might make
   you want to fix something before shipping rather than after.

2. *After step 2*, run `php artisan deploy:preflight` on the App Platform console, where the
   command now exists and the database is production by definition. The working-tree section
   stands down there, since the container runs a built image with no `.git` in it. This is the
   natural home for P4 and P5, neither of which the deploy itself changes.

P2 is the exception: it wants an answer *before* `migrate --force` runs in the start command, and
option 1 is the only way to get one. If you skip it, watch the step 2 deploy log instead - a slow
`events` rebuild surfaces there, and the fallback is to let it finish rather than to intervene.

The checks are assertions rather than queries on purpose: the comparison that matters - is any
live subscription on a price ID config no longer names - uses the same `PlanPriceUtils` the app
uses, so it cannot disagree with production through a hand-transcribed list.

If you need the raw spec rather than `deploy:preflight`'s reading of it - to check a variable
the command does not name, say:

```bash
TOKEN=$(grep '^DO_API_TOKEN=' .env | cut -d= -f2- | tr -d '"')
APPID=$(grep '^DO_APP_ID=' .env | cut -d= -f2- | tr -d '"')
curl -s -H "Authorization: Bearer $TOKEN" "https://api.digitalocean.com/v2/apps/$APPID" \
  | jq -r '.app.spec.envs[] | "\(.key)=\(.value)"' | sort
```

> **Never commit an app spec to this repo, and never `doctl apps update --spec` from a stored
> file.** `DigitalOceanService::syncDomains()` reads the live spec and PUTs it back at runtime to
> add and remove customer custom domains. Applying an older spec deletes every domain added since
> it was captured. The console's spec editor is safe because it loads the live spec first.

## The runbook

Each step carries its own verification and its own undo. Do not advance past a failed
verification.

| # | Step | Where | Notes |
|---|---|---|---|
| 1 | Create the backups bucket | DO infra | one-time |
| 2 | Deploy `main` | DO deploy | 11 migrations; two are irreversible |
| 3 | Backfill the flyer thumbnails | console command | one-time; must precede step 4 |
| 4 | Cloudflare cache rule | Cloudflare dashboard | one-time; the edge cache is inert until this exists |
| 5 | Set `BACKUP_*` and `CACHE_STORE` | DO app spec | one-time; one save, one redeploy |
| 6 | Create the `scheduler` worker, parked | DO console | one-time; runs nothing yet |
| 7 | Go live: switch the run command | DO console | one-time; **timing** |
| 8 | Disable the external cron | external | one-time; **timing**, same hour as 7 |
| 9 | Restate the docs | code | one-time |

Expect three App Platform deployments (code, env vars, worker component) plus the flyer backfill.
Everything up to step 6 can run whenever.

**Steps 7 and 8 are the one hard timing constraint**, and it is two rules rather than one:

- **Start step 7 between :02 and :20 past the hour.** The hourly tier has just fired on both
  rails and the next one is forty minutes away.
- **Never start during the 23:00 or 00:00 UTC hours.** This is wider than "avoid midnight", and
  deliberately so: step 8 has to follow step 7 *within the same hour*, so a 23:02 start puts step
  8 on top of the 00:00 daily block - the exact collision the rule exists to prevent.

### 1. Create the backups bucket - [one-time] [DO infra]

A **new private Spaces bucket**, distinct from `DO_SPACES_BUCKET`, **no CDN in front, no public
bucket policy**. Generate a key pair for it.

Everything in the images bucket is reachable by concatenating the raw storage key onto the public
CDN hostname (`ImageUtils::getUrl()`), and a backup archive contains every sale, attendee email
and phone number. `BACKUP_SPACES_BUCKET` deliberately has no fallback, and
`tests/Feature/BackupStorageTest.php` fails the build if one is re-added.

*Undo:* delete the bucket. Nothing references it yet.

### 2. Deploy `main` on its own - [DO deploy]

Console, then Deploy. This runs `migrate --force` (11 migrations) and ships all the code.

**Verify:** the deploy log shows all 11 migrations completing (this is where a slow `events`
rebuild would surface); deployment `ACTIVE`; `/admin/queue` shows no failed-job spike; spot-check
the homepage, a schedule page and checkout; `/admin` shows no new alerts. From the component
console, `php artisan schedule:list` shows a name against every entry.

Then verify the edge headers at origin with `php artisan deploy:verify --edge`. Cloudflare will
still say `BYPASS`, because no rule exists yet, but the origin half must already be right:

```
curl -sI https://eventschedule.com/pricing
  -> cache-control: public, max-age=0, s-maxage=600
  -> NO set-cookie for laravel_session or XSRF-TOKEN
curl -sI 'https://eventschedule.com/pricing?lang=fr'
  -> cache-control: no-cache, private, plus a set-cookie
```

*Undo:* console rollback to the deployment ID from P3. Note the two irreversible migrations from
P1: a rollback restores code, not data.

### 3. Backfill the flyer thumbnails - [one-time] [console command]

Do this **before** the Cloudflare rule, or cached HTML will reference the original flyers for up
to 10 minutes. The homepage poster wall was 18 MB of originals and a 28.7 s mobile LCP; the fix
only pays off once the derivatives exist. `app/Console/Commands/BackfillImageVariants.php`
generates **both** widths (`ImageUtils::VARIANT_WIDTHS = [480, 960]`) and runs **inline**, not via
the queue, so it needs no queue drain. Re-running it picks up where it left off.

```
php artisan images:backfill-variants --upcoming-only      # bounded, fixes the wall first
php artisan images:backfill-variants --limit=500          # then the rest, in batches
```

Batch it: the console session is ephemeral, and image work can push `memory_limit` on a 512 MB
box. Use `--dry-run` first for a count.

**Verify:** homepage wall images come back as `.webp` derivatives rather than originals. Allow up
to 10 minutes - the wall query is cached (`config('marketing.wall_cache_seconds')`) and recording
a variant deliberately does *not* bust that cache, so the switch is not instant.

*Undo:* none needed; a missing variant falls back to the original.

### 4. Cloudflare Cache Rule - [one-time] [Cloudflare dashboard]

Rules, then Cache Rules, then Create rule. Name `Marketing HTML edge cache`. The expression is
below; [`CACHING.md`](CACHING.md) explains the reasoning behind every clause if you need to
change one:

```
((http.host eq "eventschedule.com")
  and not starts_with(http.request.uri.path, "/admin")
  and not starts_with(http.request.uri.path, "/api")
  and not starts_with(http.request.uri.path, "/sitemap")
  and not starts_with(http.request.uri.path, "/login")
  and not starts_with(http.request.uri.path, "/sign_up")
  and not (http.cookie contains "laravel_session")
  and not (http.cookie contains "remember_"))
```

Then: Cache eligibility **Eligible for cache**; Edge TTL **Use cache-control header if present,
use default otherwise** (this is what makes `s-maxage=600` the TTL rather than a dashboard
number); Browser TTL **Respect origin TTL**.

Also set Caching, then Configuration, then **Serve stale content**. The origin deliberately omits
`stale-while-revalidate` because Firefox and Safari honour it in the *browser* cache and would
paint the anonymous copy to a freshly signed-in user; serve-stale belongs to the shared cache
only.

Both cookie clauses are mandatory. Remember-me ships **checked by default** on the login form, so
a remembered visitor with a lapsed 2-hour session sends `remember_*` and no `laravel_session`,
which is the common case rather than the exotic one. Confirmed against production: the cookie
really is named `laravel_session` (`config('session.cookie')`), so the expression is correct as
written. It breaks *silently* if `APP_NAME` or `SESSION_COOKIE` ever changes, and every signed-in
visitor would then be served the anonymous copy.

**Verify:** `php artisan deploy:verify --edge` should now be fully green. It asserts the same
contract the curls below do, plus two things that are easy to miss by eye: that no cacheable page
sends a `Vary` on `Cookie` (which would give every visitor a private cache entry, since the
`utm_*` cookies are encrypted and differ per visitor), and that no sampled apex 200 lacks a
`Cache-Control` header at all - the rule's Edge TTL is "use cache-control if present, **use
default otherwise**", so a header-less response becomes newly cacheable at the zone default.

```
curl -sI https://eventschedule.com/pricing           # cf-cache-status MISS, then HIT on repeat
curl -sI 'https://eventschedule.com/pricing?lang=fr' # stays private, cf-cache-status BYPASS
curl -sI -X POST https://eventschedule.com/marketing/visit
curl -sI https://eventschedule.com/docs/search-index.json
  -> no set-cookie on either. One laravel_session on those takes the visitor off the edge for
     the rest of their session, which is the failure the stateless-route handling exists to stop.
```

Then **sign in and click around the marketing pages in a real browser**. A signed-in visitor must
never see the guest header. This is the one failure mode no code can defend against.

*Undo:* disable the rule, then purge the zone (Caching, Configuration, Purge Everything).

**Optional and separate: the `www` to apex redirect.** `http://www.eventschedule.com/...`
currently takes two hops - `http` to `https://www.`, then `www.` to the apex. One Cloudflare
redirect rule collapses it to a single 301. Rules, then Redirect Rules, then Create rule.

Match: `(http.host eq "www.eventschedule.com")`. Then, as a Dynamic redirect:

- Expression: `concat("https://eventschedule.com", http.request.uri.path)`
- Status: `301`
- Preserve query string: on

It has to sit ahead of whatever performs the `www` to apex redirect today, and it needs the HTTP
(port 80) scheme included - that inclusion is what removes the extra hop.

### 5. Set `BACKUP_*` and `CACHE_STORE` - [one-time] [DO app spec, one save]

App-level environment variables, saved together so they cost one redeploy:

- `BACKUP_DISK_DRIVER=s3`
- `BACKUP_SPACES_KEY` / `SECRET` / `REGION` / `ENDPOINT` / `BUCKET`, pointing at the step-1 bucket
- `CACHE_STORE=database`, scope **RUN_AND_BUILD_TIME**

**Type `database` carefully.** `config/cache.php` reads `env('CACHE_STORE', 'file')`, and a second
argument never fires for a present-but-EMPTY value - so a blank entry is not a fallback to `file`,
it is `''` reaching `CacheManager::resolve()` and throwing `Cache store [] is not defined` on the
first cache read anywhere in the app. That is a site-wide 500, and `deploy:preflight` now fails
loudly on it rather than reporting it as "unset".

`CACHE_STORE` is the hard prerequisite for a second container. On the `file` default each
container has its own `storage/framework/cache/data`, so nothing serialises across them: every
scheduler `withoutOverlapping()` mutex, the seven named cross-rail locks (`translate_data_lock`,
`app_translate_lock`, `app_charge_installments_lock`, `retry_failed_jobs_lock` and the three
`*-sync-command` locks), `DigitalOceanService`'s `do_app_spec` lock (a **data-loss path on
customer custom domains**: two concurrent syncs each build from the same snapshot, and the second
PUT drops the first's domain), every `throttle:` rate limiter, and the `custom_domain:{host}`
invalidation the worker performs but the web container would never see. On `file` the
`scheduler_stalled` alert also fires permanently on a healthy install, because the heartbeat is
written where the reader cannot see it. No migration is needed; `cache` and `cache_locks` already
exist.

**Expect one extra pass of the hourly and daily blocks** in the minute after the save. Flipping
the store empties the effective cache, and `translateData()`'s `td_hourly`, `td_daily` and
`notified_pending_today` keys *are* its idempotency markers. That re-run is safe, because the
destructive commands all carry their own database watermarks (`GenerateDailyBlogPost` skips if a
post exists today, `SendSubscriptionReminders` gates on `*_reminder_sent_at`,
`NotifyRequestChanges` on `last_notified_*_count`). Still, **do not do this between 00:00 and
00:05 UTC**, where it would land on top of the day's genuine daily pass and read as a fault.

**Verify:** `php artisan deploy:verify --local` on the container reports the cache store as
`database` and round-tripping - that round trip is the check, and it fails on a store that
resolves but does not work. Then the backup round-trip: full export, emailed link, download, import into a throwaway schedule, and
finally confirm the object is **not** readable at its Spaces origin URL.

**Repeat that round-trip after step 7**, and treat this one as provisional until you have. The
failure it is guarding against is invisible from here: with `BACKUP_DISK_DRIVER` unset the export
writes to whichever container drained the queue, the job is marked complete, the user is emailed a
working-looking link, and the download 404s from the web container with nothing logged
(`BackupController::download()`). While one container does both, that cannot happen. Step 7 moves
the queue to the worker, which is exactly when it can.

*Undo:* delete the variables. Safe only while one container exists, which means through step 6,
where the worker is parked on `sleep infinity` and runs nothing. From step 7 onward this is
effectively one-way: removing it re-introduces the double-charge and double-translate hazard.

### 6. Create the `scheduler` worker, parked - [one-time] [DO console]

Create, then Create Component, then **Worker**.

| Field | Value |
|---|---|
| Name | `scheduler` |
| Source | same repo, branch `main`, dir `/` |
| Autodeploy | **off** (the service has it off; a push deploys the whole app) |
| Build command | **identical to the service's** |
| Run command | `sleep infinity` for now |
| Instance | `apps-s-1vcpu-0.5gb`, count 1 (DigitalOcean refuses count above 1 on this slug, so "never two schedulers" is platform-enforced) |
| Component env | `LOG_CHANNEL=stderr`, `SCHEDULER_RAIL=worker` |
| Alert | `RESTART_COUNT > 3` over 5 minutes |

**Do NOT set `SCHEDULER_EXPECTED_RAIL` yet.** It belongs at the start of step 7, and setting it
here is an ordering mistake earlier revisions of this file made. `SchedulerHealth::isStalled()`
treats an expected rail that has never been seen as stalled - correctly, since a worker that has
never ticked is not healthy - and a worker parked on `sleep infinity` writes no heartbeat key at
all. So saving it here turns the `/admin` banner, the nav badge and the `/admin/queue` card red
for the entire gap between step 6 and step 7, and while `scheduler_stalled` is red
`AdminAlertService` **suppresses the queue-backlog row**, so a genuine backlog would be invisible
at the same time.

Do **not** copy other secrets to component scope, since they are inherited. Do **not** add
`migrate --force`, because components deploy simultaneously and the service already migrates. Do
**not** run `optimize`.

`SCHEDULER_EXPECTED_RAIL` is the one that matters, and it is the only one that is **app-level**
rather than component scope - the *web* container is what reads it, so a component-scoped copy is
the same as not setting it. Without it, `SchedulerHealth::isStalled()` falls back to the aggregate
heartbeat key that the HTTP rail also writes, so a dead worker would be completely invisible. It
must match the worker's `SCHEDULER_RAIL` **exactly**; a mismatch is a permanent false red, and
`deploy:preflight` fails when no component announces the rail the app expects.

**Verify:** builds, stays `ACTIVE`, no restart loop, cost reads $10/mo. `php -m | grep pcntl` on
the worker (without it no queued job ever times out, and a hung one blocks its tick indefinitely).
`/admin` should still be clear at this point - if it is red, `SCHEDULER_EXPECTED_RAIL` was set too
early.

*Undo:* delete the component. It runs nothing, so nothing else is affected.

### 7. Go live: switch the run command - [one-time] [DO console]

**Timing: start between :02 and :20 past the hour, and not during the 23:00 or 00:00 UTC hours.**
Just after the hour means the hourly tier has just fired on both rails and the daily tasks are
nowhere near due. Avoiding midnight is not merely prudent: `translateData()` claims `td_daily`
with `endOfDay()`, so the HTTP rail runs its whole daily block on the first tick after midnight
UTC, the same minute the worker's twelve `daily()` tasks are scheduled for. Overlapping there
means running the daily block twice, together. The 23:00 hour is excluded for the same reason:
step 8 follows within the hour, so a 23:02 start lands it on that block.

First set the **app-level** env var deferred from step 6:

- `SCHEDULER_EXPECTED_RAIL=worker` (app level, not component scope)

Then set the run command to two lines:

```
php artisan translations:publish --no-prune || true
php artisan schedule:work
```

Line 1 exists because `storage/` comes from the image on every deploy and override files are not
in git; without it the worker sends every notification in base strings until the daily task next
runs at 00:00 UTC. `|| true` keeps a database blip at boot from stopping the scheduler starting.

**Verify:** worker logs show a `Running [...] DONE` line per due task each minute, and
`php artisan deploy:verify --local` on the web container reports the heartbeat fresh on rail
`worker`, no task in a `failed` / `overdue` / `never_finished` state, and which instance id is
actually completing tasks.

*Undo:* set the run command back to `sleep infinity`.

### 8. Disable the external cron - [one-time] [external, within the same hour as step 7]

**Disable, do not delete.** Re-enabling must be one click.

While both rails are live, the scheduler's `withoutOverlapping()` mutexes and `translateData()`'s
`td_*` keys are **different keys**, so a command can fire once on each rail. Most are idempotent
thanks to row-level watermarks, but `app:send-subscription-reminders`, the three `app:notify-*`
commands and both AI blog generators would duplicate mail, spend and posts. Only six commands
carry an explicit cross-rail lock: `app:translate`, `app:charge-installments`,
`app:retry-failed-jobs` and the three calendar syncs. Keep the overlap to minutes.

**Verify:** `php artisan deploy:verify --local` - it checks the queue depth using `available_at`
rather than `created_at` (a delayed dispatch has `created_at = now` and would report a backlog
that does not exist), the heartbeat, the per-task states and the admin alert list in one pass.
The Scheduler card on `/admin/queue` should be fresh and list the `worker` rail.

*Undo:* re-enable the cron and park the worker. A few duplicate emails beats a stopped scheduler.

### 9. Restate the docs - [one-time] [code]

Once verified, turn the two "still to do" claims into fact:

- `CLAUDE.md`, the paragraph saying `CACHE_STORE` "is currently unset there ... restate it as fact
  once done".
- [`DIGITALOCEAN_WORKER.md`](DIGITALOCEAN_WORKER.md), section 2 prerequisites and the section 4
  cutover table, annotated as applied.

## Verification after the cutover

Most of this list is one command, run twice - once from anywhere, once on the container:

```bash
php artisan deploy:verify              # edge contract + in-app health
php artisan deploy:verify --local      # on the container: scheduler, queue, cache, alerts
```

It covers the edge headers, the private pages, the two stateless support routes, the
`Cache-Control` sweep, the scheduler rail and per-task states, the queue depth and the admin
alert list. What it deliberately does NOT cover, because no automated check can:

- **Signed in, in a real browser: marketing pages render the signed-in header every time.** This
  is the one failure mode no response header can defend against - the origin refuses to *mark* a
  signed-in visitor's page public, but nothing in a response stops a shared cache *serving* them
  one it stored earlier for somebody else. Only the Cloudflare cookie-bypass rule does that.
- `cf-cache-status: HIT` on the second call - `deploy:verify` reports the status but does not
  assert it, since a cold edge legitimately MISSes.
- Worker logs showing `Running [...] DONE` lines each minute.
- Backup export, download and import round-trip works, and the object is not public at its origin
  URL. Re-do this **after** step 7, not only after step 5.
- `/admin/users` funnel numbers sit in the same range as the P6 baseline. A sharp drop means the
  beacon is not firing: check the browser console for a CSP violation, and `marketing.visit` for
  419s or 429s. A sharp rise means double counting.
- New sign-ups still carry non-null `utm_source`, `landing_page` and `referrer_url` at roughly the
  prior rate. That is the `es_attribution` cookie path.

**A deploy leaves cached marketing HTML up to 10 minutes stale, plus whatever serve-stale allows.**
For an urgent marketing fix, purge the zone after the deploy finishes. Automating a zone purge
from the release flow is a known un-wired follow-up.

## If something goes wrong

### "Scheduled tasks are not running"

Expect this alert **between step 6 and step 7** - it is not a fault there. `SchedulerHealth::isStalled()`
treats an expected rail that has never been seen as stalled, and a worker parked on
`sleep infinity` writes no heartbeat, so the banner goes red the moment `SCHEDULER_EXPECTED_RAIL`
is saved and stays red until `schedule:work` starts. That is the one window where it means
nothing.

Everywhere else it is real, and it matters more than it looks: the alert shows on the admin
dashboard, the admin nav and the Scheduler card on `/admin/queue`, and it means nothing is
draining the queue either - no email, no calendar sync, no installment charges. Both rails stamp
`scheduler.last_run_at` on every tick, even on minutes when nothing was due, so the key going
stale means the scheduler stopped rather than that nothing happened to be scheduled.

Check in this order:

1. **Is the worker component running?** Restart it from the console if not.
2. **Is `CACHE_STORE` still `database`?** On the file driver the worker writes a key the web
   container cannot read, so the alert fires permanently while the scheduler is in fact fine.
   This is the most likely false positive and the cheapest to rule out.
3. **Was the external cron throttled out?** Both cron endpoints carry their own rate-limit
   bucket, and a 429 stops the HTTP rail without any error reaching the app.
4. **Worker logs, then the per-task list on `/admin/queue`**, which names the individual task
   rather than just reporting that something is wrong.

### Emergency fallback to the HTTP cron

The one recovery path that does not require fixing the worker. Restore `APP_CRON_SECRET` to a
known value and re-enable the external cron against `GET /translate_data?secret=...` once a
minute. `translateData()` is a complete second copy of the schedule, so nothing is lost while the
worker is dealt with - which is exactly why step 8 says **disable** the cron rather than delete
it, and why rotating that secret is deferred until after a week's soak.

If both rails end up live for more than a few minutes, re-read step 8: most commands are
idempotent through row-level watermarks, but the subscription reminders, the three `app:notify-*`
commands and both blog generators will duplicate mail and spend.

### Restarting the worker

Console, the `scheduler` component, Actions, Restart. A deploy does the same thing.

`schedule:work` installs no SIGTERM handler, so a restart can hard-kill an in-flight
`schedule:run`. Every long-running command's lock is TTL-backstopped for exactly this, so the
worst case is one skipped run - but it is a reason to avoid restarting at the top of an hour if
you have the choice.

### Where to look

The worker's **runtime log in the App Platform console** is the real log. `schedule:work` streams
each `schedule:run` subprocess's output there, so a healthy worker shows a `Running [...] DONE`
line per due task per minute, and `LOG_CHANNEL=stderr` puts every `Log::` call there too.

**Ignore `storage/logs/scheduler.log`.** The `appendOutputTo()` calls throughout
`routes/console.php` never write it - that option is only honoured for process-backed scheduled
events, and every entry in that file is a closure. The file has never existed.

One thing the log will not tell you: **a task skipped because its `withoutOverlapping()` mutex
was held prints nothing at all.** The mutex registers a `->skip()` filter that is evaluated
before the `Running [...]` line, so a wedged task is indistinguishable from one that was not due.
The per-task list on `/admin/queue` is the only place it shows, and it ages from the last
*completion* rather than the last start.

## Watch for a week

- **Worker memory.** `app:send-graphic-emails` can raise `memory_limit` toward 512 MB, the whole
  box.
- **A queued job overrunning its timeout takes the whole minute's schedule with it.** `queue:work`
  runs inside `schedule:run` and SIGKILLs itself on timeout, so the tick dies, nothing else due
  that minute runs, and no heartbeat is stamped. Not new, since the HTTP rail behaved identically,
  but it is the strongest argument for a resident `queue:work`.
- Twelve `daily()` tasks fire together at 00:00 UTC, sequentially, in one container.
- **A price or copy change made at `/admin/settings` is not visible on `/pricing`** for up to ten
  minutes plus serve-stale, and there is no purge hook on a settings save the way there is a
  manual one on a deploy. Purge the zone after changing an advertised price.
- **Flyer variant generation now shares the worker's memory.** `GenerateEventImageVariants` is
  drained by the `process-queue` entry inside `schedule:run`, and decodes up to 12 MP through GD
  on the 0.5 GB box - the second consumer to watch alongside `app:send-graphic-emails`.
- **Automatic new-event announcements are live.** `announce_new_events` defaults to true and
  `role_subscribers` starts empty, so day one is quiet by construction; a real audience appears
  as guests tick the checkout opt-in. Watch for `Log::warning('Event announcement blocked')`: a
  schedule inside its own 24h SMTP failure window sends nothing, and `claimWindow()` has already
  advanced `roles.last_announced_at`, so that digest is skipped rather than retried.
- **`app:cleanup-backups` no longer collects abandoned import uploads.** Import archives live on
  the *web* container's local disk - they have to, because `ZipArchive` needs a real filesystem
  path - so the sweep now runs somewhere it cannot see them. That disk is ephemeral, so a deploy
  clears them; between deploys they accumulate. Moving import uploads to shared storage is the
  real fix and is a follow-up.
- **`app:update-geoip` now refreshes a file nothing reads.** It writes `database_path('geoip')` on
  the worker; `GeoIpService` reads it during web requests on the service. Country analytics keep
  working from the copy committed to git - they are just no longer refreshed. Note this was only
  ever intermittently useful, since that path is inside the source tree and every deploy restores
  the committed copy.
- **Ticket-panel visibility widened on deploy.** `User::canViewEventData()` replaced an owner-only
  check, so every team admin on an attached non-curator schedule can now see ticket prices,
  payment configuration and sales for that schedule's events. Intended, but it is a data-visibility
  change that takes effect the moment the code ships.

## Selfhost release

Cutting v1.0.129 for selfhosters is deliberately **not** part of the hosted deploy. Do it after
the hosted soak.

The version is already bumped in `config/self-update.php` and `.github/workflows/build.yml`.
`build.yml` is `workflow_dispatch` only and has no `needs:` on the test workflow, so publishing
does not re-run the suite.

Before publishing, note that `AppUpdateService::performUpdate()` runs `migrate --force` **inline
in the web request**, and this release's 11 migrations include the irreversible
`federated_events.event_url` drop. Release notes follow the process in `CLAUDE.md`.

## Deferred

- Rotating `APP_CRON_SECRET`. Set a new value on the app spec and confirm
  `GET /translate_data?secret=<old>` returns 403; the undo is restoring the previous secret. It
  belongs after a week's soak, so keep the old value until then - the cron fallback above is the
  only recovery path that does not involve fixing the worker, and rotating the secret disarms it.
- Adding `php artisan translations:publish --no-prune || true` to the *web* service's run command.
  Same gap, predates this change, and it edits the live service spec.
- The resident `queue:work` supervisor. It would also require removing `process-queue` from
  `routes/console.php` while keeping it on the HTTP rail, and
  `CronRailSyncTest::test_the_arguments_match_on_both_rails()` has no exception mechanism for
  that.
- Automated zone purge on deploy; moving backup *import* uploads to shared storage so
  `app:cleanup-backups` can see them again; staggering the 00:00 UTC daily block with
  `->dailyAt()`.
