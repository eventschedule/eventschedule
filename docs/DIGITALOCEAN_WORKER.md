# DigitalOcean Worker Setup

How the hosted install (eventschedule.com) runs its scheduled tasks, and the runbook for the
cutover from the `/translate_data` HTTP cron to a DigitalOcean App Platform worker.

Selfhosted installs are unaffected by everything here. They keep the single crontab entry
documented in the user guide:

```
* * * * * php /path/to/eventschedule/artisan schedule:run
```

## 1. What runs where

| Component | Runs | Responsible for |
|---|---|---|
| `eventschedule` (service) | Apache + PHP-FPM | Web requests, and backup **imports**, which run inline in the request |
| `scheduler` (worker) | `php artisan schedule:work` | Every scheduled command in `routes/console.php`, and the once-a-minute queue drain |

`schedule:work` starts a fresh `php artisan schedule:run` subprocess at the top of every minute and
streams its output to the container log. It does not wait for the previous minute to finish, so a
long task never blocks the next tick.

The queue is drained by the `process-queue` entry inside that schedule, which runs
`queue:work --stop-when-empty --sleep=0 --max-time=120 --tries=3`. That is the same invocation the
HTTP cron used, so queue behaviour after the cutover is identical to before it: dispatch latency is
up to about 60 seconds by design. See "Known limits" if that ever needs to change.

`GET /translate_data?secret=...` (`AppController::translateData()`) is the legacy rail. It is a
complete second copy of the schedule, still used by selfhost installs that cannot run a cron
process, and kept on hosted purely as an emergency restore. `CLAUDE.md` requires that any command
added to one rail is added to the other; `tests/Feature/CronRailSyncTest.php` fails the build
otherwise.

## 2. Prerequisites

Both are required before the worker exists, and neither is optional.

### `CACHE_STORE=database`

Set as an **app-level** environment variable, scope `RUN_AND_BUILD_TIME`.

`config/cache.php` defaults to the `file` driver. That is safe on a single container and unsafe the
moment there are two: every mutex the schedule relies on - `translate_data_lock`,
`app_translate_lock`, `app_charge_installments_lock`, `retry_failed_jobs_lock`, the three
`*-sync-command` locks, and every scheduler `withoutOverlapping()` - is stored in the cache. On the
file driver the worker and the web container would each keep their own copy and serialise against
nothing, so `app:translate` would buy every translation twice and `app:charge-installments` would
run two charge loops against the same cards.

It also breaks cache invalidation across containers: `app:sync-domain-statuses` forgets
`custom_domain:{host}` after activating a domain, and the web container caches that key for ten
minutes.

No migration is needed. `database/migrations/0001_01_01_000001_create_cache_table.php` already ran,
so `cache` and `cache_locks` exist. Cache is not durable state, so the switch just starts cold.

**Verify:** `SELECT COUNT(*) FROM cache;` climbs within a minute of the deploy.

### `BACKUP_*`

```
BACKUP_DISK_DRIVER=s3
BACKUP_SPACES_KEY=...
BACKUP_SPACES_SECRET=...
BACKUP_SPACES_REGION=...
BACKUP_SPACES_ENDPOINT=...
BACKUP_SPACES_BUCKET=...
```

Backup **exports** are written by a queued job and read back later by a download request. Once the
queue is drained by the worker those happen on different containers, and `storage_path('app')` is
per-container and wiped on every deploy - so exports must go to shared object storage.

> **The bucket must not be the images bucket.** Every object in `DO_SPACES_BUCKET` is reachable by
> concatenating its raw storage key onto the public CDN hostname (`ImageUtils::storedUrl()`). A backup
> archive contains every sale, attendee email and phone number for the schedules inside it. Use a
> separate bucket with no CDN in
> front of it and no public bucket policy. `BACKUP_SPACES_BUCKET` deliberately has no fallback, so
> a missing value fails rather than silently writing into the public bucket, and
> `tests/Feature/BackupStorageTest.php` fails the build if the fallback is ever added back.
>
> Export keys also carry 32 random characters, so the bucket is not the only thing between an
> archive and the public: even with the ACL loosened by mistake, the paths are not enumerable.

Backup **imports** stay on the web container's local disk and run inline, because `BackupService`
opens the archive through `Storage::path()`, which returns a bucket key rather than a filesystem
path on any remote driver.

**Verify:** run one export end to end - request it, follow the emailed link, download it, then
import it into a throwaway schedule. Then confirm the object is **not** readable at its Spaces
origin URL.

## 3. Creating the worker component

Do this in the App Platform console.

> **Never commit an app spec to this repo, and never `doctl apps update --spec` from a stored
> file.** `DigitalOceanService::syncDomains()` adds and removes **customer custom domains** by
> reading the live spec and PUTting it back, at runtime. There are currently 21 of them. Applying a
> spec written at any earlier moment deletes every domain added since. The console's spec editor is
> safe because it loads the live spec first. If you must script it: `doctl apps spec get <id> >
> /tmp/spec.yaml`, hand-edit, diff the `domains:` block, then `doctl apps update <id> --spec`.

Create → Create Component → **Worker**:

| Field | Value | Why |
|---|---|---|
| Type | Worker | No HTTP port, no ingress rule |
| Name | `scheduler` | Prefixes its log lines |
| Source | same repo, branch `main`, directory `/` | Same source as the service |
| Autodeploy | **off** | The service has it off; a push deploys the whole app, so turning it on here changes the service's behaviour too |
| Build command | **identical to the service's** | Identical build artifacts remove a whole debugging axis on cutover day |
| Run command | `sleep infinity` at first, then the two lines below | Parking it first proves the build without running anything. |
| Instance size | `apps-s-1vcpu-0.5gb` | $5/mo, and DigitalOcean's own validator refuses `instance_count > 1` on this slug - so "never two schedulers" is enforced by the platform |
| Instance count | 1 | Two instances means two schedulers |
| Environment variables | `LOG_CHANNEL=stderr`, `SCHEDULER_RAIL=worker` (component scope), and app-level `SCHEDULER_EXPECTED_RAIL=worker` | `stderr` puts scheduler errors into the DO log stream; worker-only, because setting it on the service would change how PHP-FPM logs. `SCHEDULER_RAIL` labels this container on the admin Scheduler card, because `schedule:run` cannot tell a crontab from a worker. `SCHEDULER_EXPECTED_RAIL` is the one that makes a dead worker *alert*: set it **app-level** so the web container reads it too, since it is what tells the admin panel which rail must be alive. Without it, the HTTP cron keeping the shared heartbeat fresh masks a dead worker completely |
| Alerts | `RESTART_COUNT > 3` over 5 minutes | Crash-loop detector |

The live run command is two lines, not one:

```
php artisan translations:publish --no-prune || true
php artisan schedule:work
```

The first line rebuilds the admin translation manager's overrides onto this container's disk.
`storage/` comes from the image on every deploy and those files are not in git, so without it the
worker sends every reminder and notification in the base strings until the daily task next runs at
00:00 UTC - up to a full day after an afternoon deploy. `--no-prune` keeps it to writing files;
pruning is a hand-run concern. The `|| true` matters: a database blip at boot must not stop the
scheduler from ever starting.

> The same gap exists on the **web** container and predates this change - its run command has no
> publish step either, so the admin UI also falls back to base strings after each deploy until the
> daily task fires. Adding the same line before `heroku-php-apache2` would close it. That edits the
> live service spec, so it is your call rather than part of this change.

Every other environment variable is already app-level and inherited. **Do not** copy secrets to
component scope - that creates a second place to rotate every key.

**Do not** add `php artisan migrate --force` to the run command: the service already runs it, and
components deploy simultaneously, so two `migrate` runs would race. **Do not** run `optimize`
either - it caches routes and views a worker never uses.

Cost goes from $5/mo to $10/mo.

## 4. The cutover

| # | Do | Verify | Roll back by |
|---|---|---|---|
| 1 | Deploy `main` on its own | Deployment `ACTIVE`; `/admin/queue` shows no failed-job spike | Console rollback to the previous deployment |
| 2 | Ship the backup storage change | Export → download → import round-trip; object not public at its origin URL | Console rollback |
| 3 | Add `CACHE_STORE=database` | `SELECT COUNT(*) FROM cache;` climbs within a minute | Delete the variable |
| 4 | Ship the scheduler changes | `php artisan schedule:list` shows a name against every entry; on production the "scheduler stalled" alert stays clear, because the HTTP rail stamps the same heartbeat | Console rollback |
| 5 | Create the worker with run command `sleep infinity` | Builds and stays `ACTIVE`, no restart loop; cost reads $10/mo | Delete the component |
| 6 | Change the run command to `php artisan schedule:work`, then disable the external cron **within the same hour** | Worker logs show a `Running [...] DONE` line per due task each minute; `SELECT COUNT(*) FROM jobs;` stays near zero; the Scheduler card on `/admin/queue` is fresh | Set the run command back to `sleep infinity` and re-enable the cron |
| 7 | Soak a week, then rotate `APP_CRON_SECRET` | `/translate_data` returns 403 | Restore the previous secret |

### Timing rule for step 6

Run both rails for as short a window as possible, and **never near 00:00 UTC**.

While both rails are live, the scheduler's `withoutOverlapping()` mutexes and `translateData`'s
`td_hourly` / `td_daily` cache keys are different keys, so a command can fire once on each rail.
Most are idempotent thanks to row-level watermarks (`reminder_sent_at`, `feedback_sent_at`,
graphic-email `last_sent_at`), but `app:send-subscription-reminders`, the three `app:notify-*`
commands and both AI blog generators would duplicate mail, spend and posts. The six commands that
carry an explicit cross-rail lock are safe: `app:translate`, `app:charge-installments`,
`app:retry-failed-jobs` and the three calendar syncs.

Start just after the top of an hour - around 14:05 UTC is a good slot - so the hourly tier has just
fired on both rails, and the daily tasks are nowhere near due. Avoiding midnight is not merely
prudent: `translateData` claims `td_daily` with `endOfDay()`, so the HTTP rail runs its whole daily
block on the first tick after midnight UTC, which is the same minute the worker's `daily()` tasks
are scheduled for. Overlapping there means running the daily block twice, together.

Disable the external cron rather than deleting it, so re-enabling is one click. Re-enabling during a
rollback re-introduces a short overlap; that is the right trade, because a few duplicate emails
beats a stopped scheduler.

## 5. Operating it

**Logs.** The worker's runtime log in the App Platform console. `schedule:work` streams each
`schedule:run` subprocess's output there, so you get a `Running [task-name] .... DONE` line per due
task per minute, and `LOG_CHANNEL=stderr` puts every `Log::` call there too.

Ignore `storage/logs/scheduler.log`. The `appendOutputTo()` calls throughout `routes/console.php`
never write it: that option is only honoured for process-backed scheduled events, and these are all
closures. The file has never existed. The container log stream is the real one.

One thing the log stream will *not* tell you: a task skipped because its `withoutOverlapping()`
mutex was held prints **nothing at all**. `withoutOverlapping()` registers a `->skip()` reject
filter, and `ScheduleRunCommand` evaluates `filtersPass()` before it ever reaches the `Running
[...]` line - so the task simply does not appear that minute, which is indistinguishable from it
not being due. A task wedged behind a mutex it cannot take therefore leaves no trace in the logs at
all; the admin Scheduler card is what catches it. The card asks how long it has been since the task
last **completed**, not since it last started - a run that overruns its expiry lets the mutex lapse
and a fresh copy launch, so the start time resets and only the last completion keeps ageing. The
threshold is the task's own `withoutOverlapping` expiry plus one interval of slack for the idle gap
between runs, which works out to flagging a hang roughly `expiresAt` minutes after it begins. Check
there, and check that expiry in `routes/console.php`, if a task's effects stop appearing.

**"Scheduled tasks are not running".** This alert appears on the admin dashboard, the admin nav and
the Scheduler card on `/admin/queue`. Both rails stamp `scheduler.last_run_at` in the cache on
every tick - even on minutes when nothing was due - and the alert fires when that key is missing or
older than `SCHEDULER_STALE_MINUTES` (default 20). It means the scheduler stopped, which also means
nothing is draining the queue: no email, no calendar sync, no installment charges.

What to check, in order. The Scheduler card on `/admin/queue` answers the first two directly -
its Runtime cell names the cache store, whether that store is shared between containers, and the
container actually completing tasks:

1. Is the worker component running? The Rails cell always lists the rail named in
   `SCHEDULER_EXPECTED_RAIL`, and shows *never seen* when it has yet to write a heartbeat, which
   is what a failed build or a crash loop looks like. Restart it from the console if not.
2. Is `CACHE_STORE` still `database`? On the file driver the worker writes a key the web container
   cannot read, and the alert fires permanently even though the scheduler is fine. The Runtime
   cell says so outright when the store is unshared *and* the database shows tasks still
   completing - the combination that means the worker is alive and only its heartbeat is
   invisible.
3. Was the external cron throttled out? Both cron endpoints carry their own rate-limit bucket, but a
   429 there stops the HTTP rail without any error reaching the app.
4. Worker logs for a task throwing every minute, and the per-task list on `/admin/queue`, which
   names the individual task rather than just reporting that something is wrong.

**Emergency fallback to the HTTP cron.** Restore `APP_CRON_SECRET` to a known value and re-enable
the external cron job against `GET /translate_data?secret=...` once a minute. It is a complete
second copy of the schedule, so nothing is lost while the worker is fixed.

**Restarting the worker.** Console → the `scheduler` component → Actions → Restart. A deploy does
the same thing. `schedule:work` installs no SIGTERM handler, so a restart can hard-kill an
in-flight `schedule:run`; every long-running command's lock is TTL-backstopped for exactly this, so
the worst case is one skipped run. Avoid deploying at the top of an hour if you can.

## 6. Known limits

**A queued job that overruns its timeout takes the whole minute's schedule with it.** `queue:work`
runs inside `schedule:run`, and its timeout handler is `posix_kill(SIGKILL)` on itself - so the tick
dies, every task still due that minute is skipped, and no heartbeat is stamped. Eight jobs declare
their own `$timeout`; the rest inherit 60 seconds, including `SendQueuedEmail`, so a hung SMTP
connection is the realistic trigger. This behaved identically on the HTTP rail, so it is not new,
but it is the strongest reason to move to a resident `queue:work` (see below). It also depends on
`pcntl` being present: without that extension no job times out at all and a hung one blocks its tick
indefinitely. Check with `php -m | grep pcntl` on the worker.

**`app:cleanup-backups` no longer collects abandoned import uploads.** Import archives live on the
web container's local disk (they must, because `ZipArchive` needs a real filesystem path), so the
sweep now runs somewhere it cannot see them. The web container's disk is ephemeral, so a deploy
clears them; between deploys they accumulate. The real fix is to move import uploads to shared
storage and stream them to a temp file, which is a follow-up.

**`app:update-geoip` refreshes a file nothing reads.** It writes `database_path('geoip')` on the
worker; `GeoIpService` reads it during web requests on the service. Note this was already only
intermittently useful - that path is inside the source tree, so every deploy restores the copy
committed to git. Country analytics keep working from that committed file; they are just not
refreshed. Run it on the web container, or refresh the committed file periodically.

**Graphic generation can raise `memory_limit` toward 512 MB**, which is the whole worker box.
On the service that is harmless because App Platform locks the limit on the FPM pool; a CLI process
usually has no such lock. `app:send-graphic-emails` is the reachable path. Watch worker memory
during the soak.

**Queue dispatch latency is up to about 60 seconds.** The queue is drained once a minute by the
scheduler rather than by a resident worker. This matches the behaviour before the cutover.

If that ever needs to be ~1 second instead, the change is: add a small supervisor script running
`php artisan queue:work` alongside `php artisan schedule:work`, point the run command at it, and
stand the inline drain down so the two do not compete. Keep it to one component - two workers is
double the cost for a latency improvement nothing currently needs.

**Twelve `daily()` tasks fire together at 00:00 UTC**, sequentially, in one container. The old rail
spread them across whatever minute its rolling cache key happened to expire on. If that block gets
slow, stagger them with `->dailyAt()`; `app:recheck-video-embeds` is the first candidate to move,
since it makes YouTube API calls with no time budget.

## Related

**Edge caching of marketing HTML.** Anonymous marketing pages are served with
`s-maxage=600` and no cookies, so Cloudflare can hold them. The eligibility rule, the two
Cloudflare rules the operator has to add, and the deploy caveat (a cached copy survives up
to 10 minutes past a deploy) are in [docs/CACHING.md](CACHING.md).
