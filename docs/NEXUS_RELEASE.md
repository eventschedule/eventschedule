# Nexus release runbook

How a release reaches the hosted install (eventschedule.com), and the ordered cutover for the
two pieces of infrastructure that ship with **v1.0.129**: edge caching of marketing HTML, and
moving the scheduler onto a DigitalOcean App Platform worker.

Selfhosted installs are unaffected by everything here. Cutting the GitHub release that
selfhosters update from is a **separate, later act** - see [Selfhost release](#selfhost-release)
at the end.

Steps marked **[one-time]** are the v1.0.129 infrastructure cutover and will not recur. The rest
is the standing shape of a hosted deploy.

Reference material, both authoritative and more detailed than this file:
[`CACHING.md`](CACHING.md) and [`DIGITALOCEAN_WORKER.md`](DIGITALOCEAN_WORKER.md).

## What is shipping

`main` is 44 commits and 11 migrations ahead of what is live. Deploys are manual
(`deploy_on_push` is unset on the app spec), so nothing in this release has reached production.
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

Read-only. Do all of it before touching anything.

| # | Check | Why it matters |
|---|---|---|
| P1 | **Snapshot the database.** | `2026_08_28_000000_replace_federated_event_url_with_is_online.php` **drops `federated_events.event_url`**. Its own `down()` recreates the column empty: the stored links are not recoverable, which is the point of the change. `2026_09_02_000000_reset_blog_post_updated_at.php` rewrites `updated_at` on ~161 blog rows with a no-op `down()`. Neither is reversible by a deployment rollback. |
| P2 | `SELECT COUNT(*) FROM events;` | Two ALTERs land on `events` this release: `widen_events_event_url` (varchar 255 to 500, a table rebuild on MySQL 8) and `add_image_variants_to_events`. `migrate --force` runs in the service's start command, so a slow rebuild stalls the deploy and blocks writes. On a large table, run those two by hand from the console *before* triggering the deploy. |
| P3 | Read the live app spec and record the current values | Production config is the app spec, not any `.env`. Confirm `CACHE_STORE` is still unset, `QUEUE_CONNECTION=database`, `APP_URL=https://eventschedule.com`, and note the current deployment ID for rollback. |
| P4 | In that spec, check whether `STRIPE_LEGACY_PRICE_*` / `STRIPE_LEGACY_PRICE_AMOUNTS` are set | The legacy price recognition mechanism was removed this release. Stripe price IDs are what `PlanPriceUtils` recognises a subscriber's tier and term *from*, so a live subscriber still on a retired price ID silently loses Pro or Enterprise recognition after the deploy. |
| P5 | In that spec, check `STRIPE_PRO_MONTHLY_AMOUNT` and siblings | The *defaults* changed from 9/90/29/290 to 5/50/15/150. If those vars are unset on the spec, displayed plan prices change the moment you deploy. |
| P6 | Capture a baseline from `/admin/users` | Record the "Visited site", page-view, docs and pricing funnel numbers. After the Cloudflare rule, origin-side counting stops and the beacon takes over; without a before-number a broken beacon is indistinguishable from normal variance. |
| P7 | Confirm the external cron can be disabled in one click, and record the current `APP_CRON_SECRET` | Re-enabling the cron is the only emergency fallback that does not require fixing the worker. Step 8 says **disable, not delete**. |
| P8 | `git status` clean, `HEAD == origin/main`, full suite green | `.github/workflows/test.yml` runs the whole Unit and Feature suite on push. There is **no release gate** on `build.yml`. |

Reading the app spec (P3 to P5):

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

Expect three App Platform deployments (code, env vars, worker component) plus the flyer backfill.
**The one hard timing constraint is steps 7 and 8**: start them just after the top of an hour, and
never near 00:00 UTC. Everything before that can run whenever.

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

Then verify the edge headers at origin. Cloudflare will still say `BYPASS`, because no rule
exists yet:

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

Rules, then Cache Rules, then Create rule. Name `Marketing HTML edge cache`. The expression, and
the reasoning behind every clause, is in [`CACHING.md`](CACHING.md):

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

**Verify:**

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

Optional and separate: the `www` to apex redirect rule in [`CACHING.md`](CACHING.md), which
collapses two hops into one 301.

### 5. Set `BACKUP_*` and `CACHE_STORE` - [one-time] [DO app spec, one save]

App-level environment variables, saved together so they cost one redeploy:

- `BACKUP_DISK_DRIVER=s3`
- `BACKUP_SPACES_KEY` / `SECRET` / `REGION` / `ENDPOINT` / `BUCKET`, pointing at the step-1 bucket
- `CACHE_STORE=database`, scope **RUN_AND_BUILD_TIME**

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

**Verify:** `SELECT COUNT(*) FROM cache;` climbs within a minute. Then the backup round-trip: full
export, emailed link, download, import into a throwaway schedule, and finally confirm the object
is **not** readable at its Spaces origin URL.

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
| **App-level** env | `SCHEDULER_EXPECTED_RAIL=worker` |
| Alert | `RESTART_COUNT > 3` over 5 minutes |

Do **not** copy other secrets to component scope, since they are inherited. Do **not** add
`migrate --force`, because components deploy simultaneously and the service already migrates. Do
**not** run `optimize`.

`SCHEDULER_EXPECTED_RAIL` is the one that matters and has a different scope from the other two.
Without it, `SchedulerHealth::isStalled()` falls back to the aggregate heartbeat key that the HTTP
rail also writes, so a dead worker would be completely invisible. It must match the worker's
`SCHEDULER_RAIL` **exactly**; a mismatch is a permanent false red.

**Verify:** builds, stays `ACTIVE`, no restart loop, cost reads $10/mo. `php -m | grep pcntl` on
the worker.

*Undo:* delete the component. It runs nothing, so nothing else is affected.

### 7. Go live: switch the run command - [one-time] [DO console]

**Timing: start just after the top of an hour, and never near 00:00 UTC.** Just after the hour
means the hourly tier has just fired on both rails and the daily tasks are nowhere near due.
Avoiding midnight is not merely prudent: `translateData()` claims `td_daily` with `endOfDay()`, so
the HTTP rail runs its whole daily block on the first tick after midnight UTC, the same minute the
worker's twelve `daily()` tasks are scheduled for. Overlapping there means running the daily block
twice, together.

Set the run command to two lines:

```
php artisan translations:publish --no-prune || true
php artisan schedule:work
```

Line 1 exists because `storage/` comes from the image on every deploy and override files are not
in git; without it the worker sends every notification in base strings until the daily task next
runs at 00:00 UTC. `|| true` keeps a database blip at boot from stopping the scheduler starting.

**Verify:** worker logs show a `Running [...] DONE` line per due task each minute.

*Undo:* set the run command back to `sleep infinity`.

### 8. Disable the external cron - [one-time] [external, within the same hour as step 7]

**Disable, do not delete.** Re-enabling must be one click.

While both rails are live, the scheduler's `withoutOverlapping()` mutexes and `translateData()`'s
`td_*` keys are **different keys**, so a command can fire once on each rail. Most are idempotent
thanks to row-level watermarks, but `app:send-subscription-reminders`, the three `app:notify-*`
commands and both AI blog generators would duplicate mail, spend and posts. Only six commands
carry an explicit cross-rail lock: `app:translate`, `app:charge-installments`,
`app:retry-failed-jobs` and the three calendar syncs. Keep the overlap to minutes.

**Verify:** `SELECT COUNT(*) FROM jobs;` stays near zero; the Scheduler card on `/admin/queue` is
fresh and lists the `worker` rail; the "scheduled tasks are not running" alert stays clear.

*Undo:* re-enable the cron and park the worker. A few duplicate emails beats a stopped scheduler.

### 9. Restate the docs - [one-time] [code]

Once verified, turn the two "still to do" claims into fact:

- `CLAUDE.md`, the paragraph saying `CACHE_STORE` "is currently unset there ... restate it as fact
  once done".
- [`DIGITALOCEAN_WORKER.md`](DIGITALOCEAN_WORKER.md), section 2 prerequisites and the section 4
  cutover table, annotated as applied.

## Verification after the cutover

- `curl -sI https://eventschedule.com/pricing` gives `public, max-age=0, s-maxage=600`, no
  `set-cookie`, and `cf-cache-status: HIT` on the second call.
- `?lang=fr`, `/contact` and `/browse` stay `no-cache, private`.
- Signed in, in a real browser: marketing pages render the signed-in header every time.
- Worker logs show `Running [...] DONE` lines each minute.
- `/admin/queue`: Scheduler card fresh, `worker` rail present, no per-task `never_finished`.
- `SELECT COUNT(*) FROM cache;` non-zero and moving; `SELECT COUNT(*) FROM jobs;` near zero.
- Backup export, download and import round-trip works, and the object is not public at its origin
  URL.
- `/admin/users` funnel numbers sit in the same range as the P6 baseline. A sharp drop means the
  beacon is not firing: check the browser console for a CSP violation, and `marketing.visit` for
  419s or 429s. A sharp rise means double counting.
- New sign-ups still carry non-null `utm_source`, `landing_page` and `referrer_url` at roughly the
  prior rate. That is the `es_attribution` cookie path.

**A deploy leaves cached marketing HTML up to 10 minutes stale, plus whatever serve-stale allows.**
For an urgent marketing fix, purge the zone after the deploy finishes. Automating a zone purge
from the release flow is a known un-wired follow-up.

## Watch for a week

- **Worker memory.** `app:send-graphic-emails` can raise `memory_limit` toward 512 MB, the whole
  box.
- **A queued job overrunning its timeout takes the whole minute's schedule with it.** `queue:work`
  runs inside `schedule:run` and SIGKILLs itself on timeout, so the tick dies, nothing else due
  that minute runs, and no heartbeat is stamped. Not new, since the HTTP rail behaved identically,
  but it is the strongest argument for a resident `queue:work`.
- **A task skipped by a held mutex prints nothing at all.** `withoutOverlapping()` is a `->skip()`
  reject filter evaluated before the `Running [...]` line, so a wedged task is indistinguishable
  from "not due" in the logs. The per-task list on `/admin/queue` is the only place it shows.
- Twelve `daily()` tasks fire together at 00:00 UTC, sequentially, in one container.

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

- Rotating `APP_CRON_SECRET`, the final step of [`DIGITALOCEAN_WORKER.md`](DIGITALOCEAN_WORKER.md)
  section 4. It belongs after a week's soak. Keep the old value until then, because the cron
  fallback is the only recovery path that does not involve fixing the worker.
- Adding `php artisan translations:publish --no-prune || true` to the *web* service's run command.
  Same gap, predates this change, and it edits the live service spec.
- The resident `queue:work` supervisor. It would also require removing `process-queue` from
  `routes/console.php` while keeping it on the HTTP rail, and
  `CronRailSyncTest::test_the_arguments_match_on_both_rails()` has no exception mechanism for
  that.
- Automated zone purge on deploy; moving backup *import* uploads to shared storage so
  `app:cleanup-backups` can see them again; staggering the 00:00 UTC daily block with
  `->dailyAt()`.
