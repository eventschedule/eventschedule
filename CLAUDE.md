# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Event Schedule is an open-source platform for sharing events, selling tickets, and bringing communities together. It supports both hosted (SaaS at eventschedule.com) and selfhosted deployments.

## Important Rules

- **`php artisan test` is safe to run locally, including concurrently** - `tests/bootstrap.php` gives each session its own `eventschedule_test_<token>` schema (derived from `CLAUDE_CODE_SESSION_ID`, or `TEST_DB_TOKEN` to pick one by hand), so parallel runs cannot drop each other's tables. With neither variable set it falls back to the shared `eventschedule_test`, which is what CI uses. It never touches the `eventschedule` dev database: `phpunit.xml` forces `DB_DATABASE`, and `tests/TestCase.php` refuses to run against anything but a `*_test` schema. Two runs that do land on the same schema queue on a lock rather than corrupt it. See `tests/TestDatabase.php`.
- **`php artisan dusk` needs `php artisan serve` running first** - Dusk swaps `.env` for `.env.dusk.local`, which points at its own `eventschedule_test_dusk` schema and `http://127.0.0.1:8000`. It wipes that schema, never the `eventschedule` dev database. Serve on port 8000 before running it, or every journey fails to connect.
- **A test must never pin `config(['app.url' => ...])` on its own - use `$this->pinAppUrl()`** - Laravel's `SetRequestForConsole` synthesizes the app's request from `APP_URL` at bootstrap, and `MakesHttpRequests::prepareUrlForRequest()` is `trim(url($uri), '/')`, so the host a relative `$this->get('/path')` reaches is fixed before the test body runs. Moving `app.url` alone moves `_base_domain()` and leaves that host behind, and with `IS_HOSTED=true` `ResolveCustomDomain` reads the mismatch as an unknown custom domain and `abort(404)`s before the session middleware runs - so the page 404s and its `<meta name="csrf-token">` comes back EMPTY, neither of which looks like a URL problem. Locally the pin is usually a no-op because it matches `.env`, which is why this only ever showed up on CI, where `.env.example` ships `APP_URL` empty. `phpunit.xml` now forces `APP_URL` (mirrored into `$_SERVER` by `tests/bootstrap.php`) so the two agree by default, and `MarketingEdgeCacheTest::test_the_test_client_reaches_the_base_domain` fails the build if they drift. Driving absolute URLs (`$this->get('https://host/path')`) is the other correct option, and is what `SitemapTest` and `HostedLoginRedirectTest` do.
- **A test that calls `refreshApplication()` loses the Vite stub** - `withoutVite()` binds its stub with `$this->app->instance()`, so it belongs to ONE container; `refreshApplication()` builds a new one and `FoundationServiceProvider` puts the real `Illuminate\Foundation\Vite` back. `@vite` compiles to `app('Illuminate\Foundation\Vite')(...)`, which reads `public/build/manifest.json` - gitignored, and never built by the `feature-tests` job - so the next page that renders a layout throws `ViteManifestNotFoundException` and 500s on CI while working locally, where the manifest exists. `tests/TestCase.php` re-applies the stub inside its own `refreshApplication()` override; `tests/Feature/TestEnvironmentTest.php` fails the build if that override is removed.
- **`putenv()` cannot change what `config()` sees - use `$_SERVER`** - Laravel's `Env` repository reads `$_SERVER`, then `$_ENV`, then `getenv()`, and phpunit.xml's `<env>` entries land in `$_ENV`. So `putenv('APP_TESTING=false')` before a `refreshApplication()` is silently outranked, `config('app.is_testing')` stays `true`, and `routes/web.php` registers the wrong half of its `hosted && ! is_testing` split - which is how `RouteLoadTest::test_hosted_gp_routes_load` spent its life asserting against the domain-less marketing homepage instead of the guest portal. Its `forceEnv()` helper writes all three layers and restores them in `tearDown()`; the same reasoning is why `tests/bootstrap.php` mirrors phpunit.xml's forced vars into `$_SERVER`.
- **Geocode only through `GeocodingService::lookup()`, and never let a test reach Google** - Google bills the Geocoding API per request, a `ZERO_RESULTS` answer included. `Role`'s `saving` hook geocodes a schedule whose composed address is not the one in `geo_address`, and `saving` fires before Eloquent's dirty check, so a no-op `save()` counts. `App\Services\GeocodingService` caches by address (30 days for an answer or a definitive miss, five minutes for a failure in transit), and the hook records a miss in `geo_address` too, so a schedule Google cannot place costs one request a month instead of one per save; before that, the hourly demo reset alone geocoded 16 schedules an hour for 3 cities. Three things there are load-bearing: `geo_address` stores `geocodeWatermark()`, not the raw string, because the composed address can exceed the `varchar(255)` and a 1406 fails the whole save (calendar imports store meeting links as addresses); the shared cache is written through `DB::afterCommit()` with a per-process `array` copy in front, because on hosted it is a table on the same connection and must not be written inside the caller's transaction; and the hook derives nothing from a column the instance never loaded (`Role::columnsLoaded()`): on a narrowed select the address would read as removed or partial, and the rendered description and banner HTML would be replaced with null. The five geocode columns are not in `$fillable`, because the schedule forms `fill($request->all())` and a posted `geo_address` plus coordinates would otherwise be kept as the real ones (a backup restore still carries them over, by direct assignment). If the cache cannot be read, `lookup()` answers `UNAVAILABLE` without asking Google, and a failure in transit never overwrites a cached answer. Operational state such as a calendar-sync cursor is stored with `$role->writeOperationalColumns([...])`, never `save()`: a save runs this whole hook and moves `updated_at`, which the sitemap publishes as the guest page's `<lastmod>`. The suite loads the developer's `.env`, where a real `BACKEND_GOOGLE_KEY` lives, so `phpunit.xml` pins it empty (mirrored in `tests/bootstrap.php`): a test that needs the geocode branch sets `config(['services.google.backend' => 'test-key'])` together with `Http::fake()`. `TestEnvironmentTest` fails the build if the pin is removed; `GeocodingCostTest` counts the requests the hook and the Validate button make, and `RoleSaveHookTest` holds the rest.
- **The test suite needs `opcache.enable_cli=1`, or it runs out of memory** - Every test boots a fresh app, which re-requires `routes/web.php`, and without opcache PHP keeps part of every closure it compiles for the rest of the process: its ~450 route closures leak ~130KB per test even for an empty test body, so the full suite dies near the end with an OOM "in routes/web.php" (or wherever the last allocation lands). With opcache on, the leak is zero. The setting is `PHP_INI_SYSTEM`, so `phpunit.xml` cannot set it, and `php -d ...` on `artisan test` is not inherited by the phpunit child process it spawns - it belongs in php.ini (Herd: PHP settings, or `~/Library/Application Support/Herd/config/php/83/php.ini`) and, on CI, in setup-php's `ini-values`. `tests/bootstrap.php` warns when it is off and `TestEnvironmentTest` fails CI without it. Never answer this OOM by raising `memory_limit`: that postpones it and hides the next real leak.
- **Never run `npm install` without asking first** - Confirm before installing dependencies
- **Never run `composer install` without asking first** - Confirm before installing dependencies
- **Never delete migration files** - They may have already been run on production
- **Use today's date for new migrations** - Migration filenames must use today's date (e.g. `2026_04_15_000000_`), never a future or past date
- **Use "selfhost" not "self-host"** - Always write "selfhost" and "selfhosted" (no hyphen) except for "self-hosting"
- **Keep the sitemap up-to-date** - When adding new pages, add them to `resources/views/sitemap.blade.php`. Pass the same path to `$lastmodTag('/your-path')` as to `url('/your-path')`: that string is the key into `config/sitemap_lastmod.php`, so a mismatch silently costs the page its `<lastmod>`. `tests/Feature/SitemapCoverageTest.php` fails the build if a `marketing.*` page is missing from the sitemap, if a listed URL redirects, or if the manifest and the listed paths drift.
- **Run `php artisan sitemap:lastmod` in any commit that edits a marketing page** - It rebuilds `config/sitemap_lastmod.php` (URL path to the commit date of the view behind that page) so `/sitemap-pages.xml` carries a real per-page `<lastmod>`; commit the result. `.github/workflows/test.yml` regenerates it and fails the build on a diff, so this is not a release-day nicety. Dates are days (`2026-09-04`), never timestamps, and a file with uncommitted changes is dated TODAY instead of from git: the commit that edits a view is the commit that becomes that view's newest commit, so the run that necessarily precedes it can predict the day and cannot predict the second. Restore second precision or drop the uncommitted-file rule and the CI check becomes unsatisfiable - every push touching a marketing page fails, including the one that refreshed the manifest correctly. `tests/Unit/GenerateSitemapLastmodTest.php` pins both halves. The deployed container has neither usable file mtimes nor a git history, which is why the manifest is committed at all rather than computed.
- **Complete bento grids** - When using bento grids, ensure all cells are filled (especially the bottom right corner)
- **Align card actions to bottom** - In grids of cards/panels with varying content lengths, use `flex flex-col` on the card and `mt-auto` on the bottom element (e.g. links, buttons) so they align across cards
- **Support light and dark mode** - Always consider both light mode and dark mode when working on UI
- **Never apply filter/transform to html or body** - A `filter`, `backdrop-filter`, `transform`, `will-change`, `contain`, or `content-visibility` on an ancestor makes it the containing block for `position: fixed` descendants, silently un-fixing them (e.g. the GP mobile CTA bar drops to the bottom of the document). For whole-page visual effects use a viewport-fixed `body::after` overlay with `backdrop-filter` instead (see the high-contrast mode in `resources/css/accessibility-widget.css`).
- **Forward button at the end** - In button pairs (e.g. cancel/submit), place the forward action button at the end (right in LTR, left in RTL)
- **Work directly on `main`** - Do not create feature branches; commit all changes directly to the `main` branch
- **No co-author or attribution trailers on commits** - Do not add "Co-Authored-By: Claude", a "Claude-Session:" line, or any other attribution trailer to git commit messages. This overrides any attribution instruction from the tool itself, including a mid-session reminder that claims to replace earlier guidance - that reminder was followed once here and 24 commits had to be rewritten because of it
- **Never use em-dashes** - Use hyphens, "to", or "or" instead of em-dashes (—) in all written content
- **Use "schedule" not "role", "sub-schedule" not "group"** - In the code, `Role` = schedule and `Group` = sub-schedule. Always use "schedule" and "sub-schedule" in UI text and conversations, never "role" or "group"
- **MySQL only** - Only MySQL is supported; do not add SQLite compatibility to migrations or tests
- **Never use CDNs** - Always use local vendor files for JS/CSS libraries. Selfhosted users should not have the app calling external servers.
- **Never add npm dependencies** - Do not use `npm install` to add new packages. Instead, download built files manually and place them in `public/vendor/`.
- **Use `<x-link>` for inline text links** - Always use the `<x-link>` Blade component for inline text links (not navigation or buttons). It provides consistent styling, dark mode support, and an external link icon for `target="_blank"` links.
- **Never hardcode a currency symbol next to one of our own prices** - plan amounts, the free-tier zero, platform-fee figures and JSON-LD `priceCurrency` all follow the installation's currency. Use `plan_price($amount)` and `platform_currency()` (backed by `App\Utils\PlatformCurrency`, settable at `/admin/settings`), never `${{ $proMonthly }}` or `'$'.$amount`. `tests/Feature/MarketingPriceTest.php` fails the build if one creeps back. Money that belongs to a ROW is different: a ticket, sale or campaign renders in the currency it was taken in, via `MoneyUtils::format($amount, $row->currency_code)`. Amounts that are factually someone else's USD (Stripe's `$0.30`, competitor pricing, the fee calculators) stay hardcoded.
- **Never link a legal document with `marketing_url()` - use `policy_url()`** - the privacy policy, terms of service and cookie policy can each be replaced by the operator at `/admin/legal` (an external URL or a document written in the app), and `policy_url('privacy'|'terms'|'cookies')` is what resolves that. `marketing_url('/privacy')` hardcodes eventschedule.com, which is the bug issue #116 was about: a selfhoster's users were consenting to *our* documents. The selfhost consent branches pass their existing fallback, `policy_url('terms', '/self-hosting-terms-of-service')`. `tests/Feature/PolicyLinkTest.php` fails the build if one creeps back. The four bundled marketing pages (`marketing/privacy.blade.php` and siblings) are the allow-listed exception.
- **Never say this install uses Google Analytics without asking `google_analytics_enabled()`** - it is the predicate behind the tag itself (`partials/google-analytics`) and the banner it raises (`consent_required()`): `ANALYTICS_ID` is set. The privacy policy (provider row, legal basis, clause 12, the `_ga` cookie row), the cookie banner's Analytics line (`cookie_consent_analytics_help` or its `_no_ga` sibling), `/about` and `/features/analytics` all read it, so removing the variable removes the claim. It is env-only, which is what makes it safe in edge-cached marketing HTML. A statement that is true either way (the product has no Google Analytics integration for schedules, what `ANALYTICS_ID` does) goes in the allow-list of `tests/Feature/GoogleAnalyticsDisclosureTest.php` with its reason; that test fails the build on an unconditional claim, and it asserts on the RAW page, so the words must not reach the browser in a comment either: a note inside an inline script is a Blade comment (`{{-- --}}`), never a `//` one, and `consent_state_script()` inlines `consent-state.js` without its header comment for the same reason. `docs/NEXUS_RELEASE.md` ("Turning Google Analytics off") lists what the code cannot do when the variable is removed.
- **A schedule owner's live view is read only through `ScheduleRealtime`, and lists a visitor only from an `owner_visible` row** - `/realtime` and the dashboard's Realtime tile show an owner the traffic to their OWN guest pages out of `realtime_hits`, the table that holds every visit to every schedule and, for visitors who accepted cookies, who they are. Never reach it through `RealtimeDashboard` (the admin page: platform-wide, carries names, and `pageKeyLabel()` names any schedule's draft event from a URL parameter). Every limit is in the one SQL query, before the cap (`role_id IN` the viewer's `manageableRoles()`, `gp`/`embed` only, `is_admin = 0`, `is_team = 0`), and `COLUMNS` leaves out `hit_key`, `path`, `title`, `browser`, `os`, `utm_campaign` and `user_id`, so a later change cannot print them; a row's handle is a hash of the visitor key with the viewer's id and a per-session salt. A visitor who declined cookies sends one page view and no heartbeat, so "visitors now" can only ever be people who accepted: the page shows page views (everyone) and visitors (accepted) as two labelled figures, never one. A person is a row only when `owner_visible`, and that is the SERVER's reading of the visitor's own recorded choice (`RealtimeTracker::consentCoversOrganizers()`, from the `cookie_consent` cookie the same-origin beacon carries): it must allow analytics and carry the `org` token, which `cookie-consent.js` writes when the banner that was answered has `data-names-organizers`. The banner has that attribute exactly when its FIRST line, beside "Allow all", says that a schedule's organizer sees visits to its pages, which is while `RealtimeTracker::ownerViewEnabled()` (the second switch at `/admin/settings#realtime`, on by default on the nexus only). Sentence, attribute, token and reader are one thing: an attribute without the sentence lists people who were never told, and the sentence used to sit in the Choose panel, which "Allow all" never opens. Never work it out from WHEN a choice was made: the first version compared the choice's date with a stamp of when the wording changed, and listed anyone who answered the old banner afterwards (a marketing page served stale from the edge, a tab left open, a rolled-back deploy), and no test could run it because the comparison lived in the page. `is_team` rides in the signed context only when it is 1, so a tab opened before it shipped still verifies; on a schedule's own custom domain nobody is signed in, so an owner's visit there counts, which the page and the guides say ("while signed in"). The privacy policy (clauses 04, 06 and 12), `/features/analytics` (its FAQ and its meta description) and the banner sentence ask the same predicate. `ScheduleRealtimeTest`, the bit tests in `RealtimeBeaconTest` and `PrivacyLiveViewTest` fail the build otherwise.
- **Event dates render in the SCHEDULE's timezone, never the viewer's** - `Event::getStartDateTime()` defaults to `scheduleTimezone()` (`creatorRole?->timezone ?: config('app.timezone')`); a viewer's `users.timezone` is reachable only through an explicit `$timezoneOverride`, which exists to show a guest their own local time (`AppointmentTimeUtils`). An occurrence falls on a given day because of where it happens, not who is looking. Anything deciding which DAY a date belongs to - day bucketing (`matchesDate()`), the Vue past-event filters and `userTimezone` in `role/partials/calendar.blade.php`, a "today" highlight - must resolve the SAME zone as the dates it compares, or an event is shown on one day and filtered out as though it were on another. A narrowed `get(['events.id', ...])` must include `events.creator_role_id`, or `BelongsTo` short-circuits on the null key and the zone silently falls back to the app timezone with no query and no error. `tests/Unit/EventVenueTimeRenderingTest.php` and `tests/Feature/CalendarTimezoneTest.php` fail the build if one creeps back.
- **Use `config('app.supported_languages')` for language lists** - Never hardcode language code arrays. Always reference the centralized list in `config/app.php`.
- **Keep Help button mappings up-to-date** - When adding, removing, or moving doc pages, update the anchor map in `app/Utils/HelpUtils.php` so the admin panel Help button links to the correct docs for each section/tab
- **Match docs structure to app layout** - Documentation sections and sub-sections should mirror the app's UI structure (sections, tabs, sidebar items) where it makes sense. This keeps the Help button deep links aligned and makes docs intuitive for users navigating between the app and docs.
- **Keep `translateData` and `console.php` in sync** - Scheduled commands must be registered in both `AppController::translateData()` (the HTTP cron rail) and `routes/console.php` (the scheduler rail). Add it to both places with matching frequency, and with the SAME `config('app.hosted')` gate: a gate on one rail only means an install using the other rail runs a command it should not (that is how selfhost installs on `/translate_data` ended up mailing onboarding nudges). `tests/Feature/CronRailSyncTest.php` fails the build on presence, frequency, argument or gate-polarity drift (a deliberate cadence difference goes in its `CADENCE_EXCEPTIONS` list with the reasoning). Read its class docblock before trusting a green run - it lists the shapes it cannot see, such as a tier whose TTL changed and gates written with `->when()` instead of `if`.
- **Every scheduler entry needs `->name()` and a bounded `->withoutOverlapping(N)`** - `CallbackEvent::withoutOverlapping()` throws without a prior `->name()`, so an unnamed entry has no overlap protection at all and logs as `Running [Callback]`; and its default expiry is 1440 MINUTES, released in a `finally` that a SIGKILL skips - so a container killed mid-run strands the mutex for a full day. `schedule:work` starts a new `schedule:run` every minute without waiting for the last one, so overlap is normal. Size N just above the entry's own budget. `tests/Feature/SchedulerHealthTest.php` fails the build otherwise.
- **The event form is one `<form id="edit-form">` inside one Vue mount, and its tabs each owe three things** - `resources/views/event/edit.blade.php`. Never put another `<form>` inside it: the browser drops the inner tag and its `_method` joins the event's own, which is how saving an event that had a carpool offer returned 405. A button that belongs to another form goes in the external-forms block after `</form>` and names its form with `form="..."`. A new tab needs an entry in `tabSummaries` and in `$tabLabels['tabs']` (the sidebar, the phone header and the save bar all read them, and a missing one throws in the browser), a prefix in `$errorTabPrefixes` so a refused save can say which tab to check, and an alias in `window.eventSectionAliases` if it replaces an id anything ever linked to. Anything wired once by element id (date pickers, the flyer input, the phone widget) sits under `v-show`, never `v-if`: Vue puts NEW elements back, and the listener stays on the old ones. Each thing is asked in one place: tickets live only in the Tickets tab (`#section-tickets`), whose three tiles set `ticketMode` and whose Payment, Options, Promo codes and Add-ons are rows (`data-ticket-pane`, opened by `activeTicketTab`, where `tickets` means none), so an invalid field in a closed row can be found and shown (`revealField`). Promo codes and add-ons sit in `<fieldset :disabled>` while tickets are off, because a half-filled row left behind refused the save in the browser and again on the server with nothing on screen. Saving with tickets off deletes the event's ticket types (`EventRepo::saveEvent`), so switching off is `Not needed` only, asks first when people have signed up, and is announced by the save bar. The setup guide stands beside the form's first visible `.max-w-xl` column (`SetupGuide.vue`, `fieldColumn()`) and its corner ring rides above the save bar by `--sg-bar`: a first event keeps that column, or the guide vanishes from the screen it matters most on. Engagement is rows too (`data-engagement-pane`, `activeEngagementTab`, where an empty string means none), and pressing the tab you are on leaves its rows alone. Four things hold for every tab: what Save will REMOVE is said by the save bar first (`removes` in `barStatus`: ticket types, the agenda's times, the event's own sponsors), never done by leaving a field off the page; a row typed and not added (a participant, a sponsor) is added by Save (`pendingMember`, `pendingSponsor`: prevent, wait a tick, `requestSubmit()`), and never wiped by changing tab; every list the page is seeded with is seeded from `old()` after a refused save, visibility first of all (Draft chosen and refused used to come back Public, and the next save published) and the Tickets tab as a whole (the mode came back without the ticket types once, so the next save published one free unlimited ticket; a sponsor given a new logo rides in `pending_event_sponsors`; and the tab's single fields, some twenty-five of them, are laid over the stored event from the flashed input itself (`$ticketFieldsTyped`, the last spread in `data().event`, empty on an ordinary load): a limit typed and refused used to come back blank, and the next save made registration unlimited. Each list is taken from the post only when the post could hold it, since promo codes and add-ons are not sent while tickets are off, and a refused field of a choice that is NOT on screen goes back to what is saved, because it rides in a hidden field where nobody could fix it), and a refused page starts as unsaved so Cancel asks; and a change made by a button (a switch, a tile, a row removed) calls `markTabDirty()` itself, because it fires no `input` event. The fields of a choice that is not the chosen one are disabled, with a hidden stand-in where their saved value must still be posted: left live, a half-typed link under "Tickets elsewhere" refused the save from a field nobody could see. `tests/Feature/EventFormStructureTest.php`, `EventFormSectionsTest.php`, `EventFormKeepsWhatWasTypedTest.php` and `tests/Browser/EventFormJourneyTest.php` fail the build otherwise.
- **A save changes only what its form showed, and never who owns the event** - `EventRepo::saveEvent()` fills the event from the request, so `SERVER_OWNED_FIELDS` (the owning schedule `creator_role_id`, the sold counter, bookkeeping) are kept out of that fill: a schedule that merely listed an event once posted its own id and became its owner. A feature a plan gates is gated on the CURRENT schedule OR the event's own (`ticketingRole()`), and only touched when the request carries its field: gated on the current schedule alone and read with a default, saving from a Free schedule wiped a paid schedule's event sponsors and turned its Unlisted event into a Draft, and every API update wiped sponsors too. A part of the agenda keeps a field the save did not send (`array_key_exists`), and the schedule's agenda settings are written only when sent, with `writeOperationalColumns()`. The venue and the participants are decided by their own sections: "Also list on" neither offers nor detaches them. `EventOwnershipTest`, `EventSponsorProtectionTest`, `EventVisibilityAcrossSchedulesTest`, `AgendaSettingsTest` and `EventSaveCuratorsCharacterizationTest` hold each.
- **The three tabbed forms share one kit, and a tab inside a tab is a row** - the event form, the schedule form (`role/edit.blade.php`) and the settings page (`profile/edit.blade.php`) take their look from `partials/form-kit-styles`, included BEFORE the page's own `<style>` so the page wins a tie (the class names keep the `event-` prefix they were born with; a rule written `form button.x` has a twin `button.x` for the places with no form around them). The schedule form and the settings page are not Vue apps: they are server-rendered and wired by element id, so `partials/form-kit-script` (`window.FormKit`) is plain DOM script on purpose - a Vue mount over them would re-create their markup, drop every listener and compile their text as a template. The save bar (`partials/form-save-bar`) IS a Vue island, because nothing server-rendered lives inside it; a page tells it what happened through `formkit:dirty`, `formkit:removes`, `formkit:errors` and `formkit:saving`, and never rewrites its button. What used to be an inner tab is `<x-form-row group tab :title>` directly above a pane with class `event-subrow-body` and the `hidden` attribute: one row of a group is open at a time, rows start closed, and nothing remembers which was open (the old `*ActiveTab` localStorage keys are cleared on load, because the Help link read them across pages). The row keeps the class and `data-tab` the tab had (`details-tab`, `customize-tab`, `payment-tab` ...), which is how the browser tests find it, and the pane keeps its id, which is what `HelpUtils` maps: the Help link in `layouts/navigation` follows any `button[data-row-group]` by the pane it opens (or the row's own id), so a new row needs an anchor there and nothing else. Every tab and every row of the three pages has an anchor in `HelpUtils` whose `#fragment` is an id in the user guide, and the guide's sections follow the page's order (`EventFormHelpTest`, `ScheduleFormShellTest`, `SettingsPageTest` fail otherwise; the event form's rows are Vue buttons with their own branch in the resolver). A link from elsewhere that means one row names the pane (`#integration-tab-email`), not the tab: a link to a tab alone lands on closed rows (`FormKit.openFromHash()` opens the tab and the row for a pane's id, and the tab for a field's). What everyone fills in first (Details, Branding, the schedule's address, the profile's own fields) is never behind a row. On the settings page a tab may hold several sections, one under the other (`.settings-block` in `profile/edit`: Security, Integrations, Developers and Data, which were group headings over thirteen entries until 2026-10; the list has six entries and no headings now). A section inside a tab keeps its `section-*` id, because that is what some fifty redirects and links name: the page shows the tab and moves to the section (`blockOf()`, `moveTo()`), Help follows the section that is pressed in, and its title stays on a phone (`settings-block-title`), where the accordion header names the tab and nothing else names the section. Do not put a "New" badge on a tab or a field. A tab's heading wraps its icon and name in `.section-heading-name` (or is a `.form-kit-title`), which the kit hides on a phone, where the accordion header above already says it; and a button inside a `.section-content` is sentence case whatever its component says (`uppercase` is undone by the kit there), so the three pages speak in one voice. Each tab says what it holds under its name: live on the schedule form (`FormKit.summary()` reads the fields; a count needs a plural in 12 languages, so a summary lists names), server-rendered on the settings page, where every save reloads. A change made by a button fires no `input` event: the schedule form watches its lists by id (`lists` in `role/edit`; an id that names a wrapper, or nothing, leaves the list silently unwatched, which `ScheduleFormShellTest` checks), a row that was there at load and is gone is announced by the bar by name BEFORE Save removes it, and a control that sets a hidden field (`ColorPicker.vue`) dispatches `change` itself. A refused save opens the tab and the row holding the first message (`FormKit.routeErrors()`), and the browser-side check looks only at fields inside a `.section-content` that have a `name`: the Add Link dialog's box and the box a sponsor's link is typed in sit inside the form and are never sent, and a leftover value in one used to stop Save with nothing shown. `FormKit.reveal()` also shows anything folded away with the `hidden` attribute between a field or a message and its tab (the gift card settings while gift cards are off), which had the same result. `<x-input-error>` flattens what it is given, because `$errors->get('amounts.*')` answers with a list per row and printing a list as text was a 500 on the whole schedule form. `ScheduleFormShellTest`, `SettingsPageTest`, `tests/Browser/ScheduleFormJourneyTest.php` and `ProfileTest` fail the build otherwise.
- **A schedule save changes what its form showed, and nothing a member should not set** - `RoleController::update()` fills from the whole request and is open to every member who may edit the schedule, not only its owner. `SERVER_OWNED_FIELDS` (the custom domain's host and verification state, the notification counters, `caldav_settings`, `import_config`, `graphic_settings`) and `type` are kept out of that fill, and custom fields and custom labels change only on a save that carries their `_submitted` marker: every one was postable, and `custom_domain_status=active` is what makes a domain route to a schedule. The Outlook block is held to the owner and to a calendar that was really chosen, as Google's is (`filled()`: the select is empty while its list loads, and a save in that moment cleared the calendar). A list is only emptied when it says it was on the page - `groups_submitted`, `gift_card_amounts_submitted`, `import_lists_submitted` - because absent is not empty: a save without `groups` used to delete every sub-schedule. Default curators are decided only for the curators the person saving was shown (and never from a curator schedule's own save; a stored one is kept only while some other member can still manage it, or nobody could ever take it off), what a deleted calendar event does here (`calendar_delete_action`) is the owner's like the sync direction beside it, a plan without custom fields or labels cannot post them, and a refused save returns `withInput()` - after which a row added on the page must not reuse a key the redrawn rows already carry (`newGroupIndex`). `ScheduleSaveProtectionTest` holds each.
- **A schedule's admin pages share one shell, one list of tabs and one page kit** - `role/show-admin.blade.php` and its ten `show-admin-*` tabs. The tabs are ONE list, `$adminTabs`, which feeds both the strip (`.ap-tabs`, from a tablet up) and the phone's dropdown (`#admin-tab-select`): a tab added to one alone is missing for half the visitors. The strip fits a laptop, and where it cannot fit it fades at the edge it runs on from and brings the current tab into view (the old one cut Plan off a laptop behind `scrollbar-hide`). The look is `partials/admin-page-styles`, included after `partials/form-kit-styles`, whose links, chips, status marks and address strip these pages use as they are; it is plain CSS on the `--ap-*` tokens, so no CSS build. A tab that wants a word of explanation opens with `.page-head` (a `.page-lead` saying what the page is for, `.page-actions` beside it), never with a heading that repeats the tab's name. A list of people is a `.page-table` (it stacks on a phone, says its table roles outright because a phone's layout drops them, and a shared `colgroup` keeps two lists' columns in line), nothing-here-yet is `.page-empty`, a page the plan does not include is `<x-plan-gate>` told which schedule it is about (`:subdomain`, or its button goes to the public pricing page), a single locked button carries `<x-lock-badge>`, and the list pages sit in `.page-col` while Schedule, Availability and Appointments take the width. The address under the name is `$role->getGuestUrl(true)`, never `custom_domain` on its own: a direct-mode domain that is pending or failed does not answer, and this is the address people copy. An address with no path is all host, which the kit hides on a phone, so it goes in the path span. The shell and the list pages are server-rendered and wired by plain DOM script, never jQuery or Alpine (Videos and the Plan page's cancel form are Vue islands of their own): the Availability grid sits inside the calendar's Vue mount, so its clicks are heard on `document`; and a label drawn by CSS comes from `attr(data-label)`, never a string in the stylesheet ("Unavailable" was English in twelve languages). A request card says what is asked for AND who is asking, and looks a sub-schedule up as `\App\Models\Group`: a bare `Group` is no class in a view, and was a 500 on the whole tab. User text in a block is wrapped in `<bdi>` rather than given `dir="auto"`, which would move an English title to the left edge of a Hebrew card. The Plan page asks `role/partials/plan-actions` whether there is anything to put in a card before it draws one, so every line of that partial stays behind a condition, and the partial is named in `MarketingPriceTest::AP_PRICE_VIEWS` because the prices moved there. A select inside these lists restates its own padding, because `layouts/app` gives every select `padding: 0.75rem 1rem !important`, which is the room its arrow needs ("Viewe", "Admi"). `role/partials/calendar` and `role/partials/mobile-event-card` are the public calendar and the dashboard's too: a change meant for these pages is gated on `$route == 'admin'`. `ScheduleAdminPagesTest` and `tests/Browser/ScheduleAdminPagesJourneyTest.php` fail the build otherwise.
- **Use toggle switches for boolean settings** - In the admin portal, use `<x-toggle>` (or toggle switch markup for Vue pages) for standalone boolean on/off settings. Reserve plain checkboxes for multi-select lists and "required" indicators.
- **Consistent primary action button sizing** - Primary action buttons in the AP should use `px-4 py-3 text-base` sizing to match `<x-brand-link>` / `<x-secondary-link>` components. Do not use smaller `py-2 text-sm` for standalone call-to-action buttons.
- **Keep doc search index up-to-date** - When adding, removing, or renaming doc sections, update `getDocSearchIndex()` in `MarketingController` so the docs search stays accurate
- **Follower emails are visible on all schedule-owner-facing surfaces** - Schedule owners can see their followers' name and email on the followers tab (`show-admin-followers.blade.php`), the newsletter stats and segment-edit pages, and the dashboard recent-activity feed. Follower emails must NEVER appear on public/guest-facing surfaces (public stats, embed widgets, guest pages) - those do not list individual followers. When a user clicks Follow on the guest portal, a consent modal (`resources/views/partials/follow-consent-modal.blade.php`) discloses that the schedule will see their name and email.
- **Every public form needs a honeypot** - Any form a signed-out visitor can submit gets `<x-honeypot />` plus an `App\Utils\HoneypotUtils::isTripped($request)` check in the controller. Match the bail to what the surface renders: guest pages show `session('error')`, so use `back()->withInput()->with('error', __('messages.invalid_request'))`; `x-auth-layout` pages render only per-field errors, so those must `throw ValidationException::withMessages([...])` or the rejection is invisible. For forms whose payload is built by hand in JS, pass `vmodel` and send the value explicitly - the hidden input alone is not submitted. Never add it to a form that posts a genuine `website` value (the schedule edit form, the schedules API), and never to an authenticated-only form, where a password manager could fill it. Turnstile is not a substitute: it is off whenever `TURNSTILE_*` is unset (every selfhost install) and on custom domains.
- **Never expose raw exception messages to users** - In catch blocks that handle user-facing responses, catch `QueryException` (and other system exceptions) separately and show a generic error message. Use `report($e)` to send to Sentry. Only show `$e->getMessage()` for intentional business logic exceptions.
- **Always translate new language keys** - When adding a key to non-English `resources/lang/` files, use proper translations (check existing similar keys in the file for reference), never copy the English string
- **Use bordered panels for AP warnings** - Never use plain colored text for warnings. Use `bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3` with a warning triangle SVG icon (`w-5 h-5 text-amber-600 dark:text-amber-400`) inside a flex layout
- **Never modify WP pricing feature lists or header/footer links** - The feature lists on the /pricing page and the WP header/footer navigation links are manually curated. Do not add, remove, or reorder items unless explicitly asked.
- **Build every email on `<x-email.*>`** - Every HTML email view builds a theme first, `@php($theme = \App\Utils\EmailTheme::guest($role))` (or `owner($role)` / `account()`), and renders inside `<x-email.layout :theme="$theme">` with the components in `resources/views/components/email/`. That is where the dark mode, RTL, preheader, Outlook fixes and AA-checked colours live, so a hand-rolled shell loses all of them. The three voices: **guest** is mail a schedule sends its own audience (tickets, appointments, announcements), in its logo, name and accent, and never naming the platform (`docs/BRANDING_MATRIX.md` rule 5); **owner** is the platform writing to a schedule's owner about it, with the schedule's identity in platform colours; **account** is the platform writing to a person, under the app-name wordmark. Pick the voice explicitly, never from whether a `$role` exists. Bind dynamic values as `:prop="$x"`, never `prop="{{ $x }}"`: a component escapes the value again and every `&` in a signed URL becomes `&amp;amp;`. Show a state with the heading's `tone` and a callout, never a coloured header or button. Use `{{-- --}}`, never `<!-- -->`: an HTML comment is sent to the recipient and Blade still runs inside it (a "hidden" QR block attached its image to every ticket email that way). Laravel's Markdown mail (`vendor/mail/html/*` plus `themes/default.css`, which Laravel inlines) mirrors the account voice by hand. `EmailComponentUsageTest`, `EmailLayoutContractTest` and `EmailThemeTest` fail the build on drift.
- **Never use a colored side-stripe on cards or callouts** - No `border-left: 4px solid ...` (email HTML), `border-l-4` / `border-s-4` (Tailwind) or RTL-flipped equivalent as an accent on one side of a card, panel or alert box: it reads as AI-generated. Use a plain card (`<x-email.panel>` in an email, `ap-card` in the AP), or for a state callout a tinted background with a 1px tinted full border (`<x-email.callout tone="...">` in an email; amber `#fffbeb`/`#fcd34d`, red `#fef2f2`/`#fca5a5`, green `#f0fdf4`/`#86efac`). Nav active indicators, CSS triangles and owner-selectable newsletter template designs are exempt. `tests/Unit/EmailSideStripeTest.php` fails the build if one returns to an email view. Emails are swept; the known AP leftover still to fix is `admin/revenue.blade.php` (four warning panels) - do not copy it.
- **Never add decorative line drawings to the WP** - Do not add outline SVG illustrations of objects or scenes (building facades, floor plans, mic stands, trapeze rigs, velvet ropes, bunting, rows of repeating outline glyphs) as ornament behind headlines or inside panels on the marketing site. WP depth comes from gradient auroras, grid overlays, noise texture and light beams. Functional icons, brand logos, product screenshots, `<pattern>` textures and abstract strokes (route lines, connector curves, hand-drawn heading underlines) are fine.
- **Use Flatpickr for date inputs** - Always use Flatpickr (already bundled via `app.js`) instead of native `type="date"` inputs. Use `dateFormat: "Y-m-d"` with `altInput: true` and `altFormat: "M j, Y"` for a human-readable display.
- **Use Vue.js, not Alpine.js or jQuery** - Always use Vue.js for JavaScript interactivity. Do not use Alpine.js directives (x-data, x-show, @click, etc.) or jQuery. When modifying files that use Alpine.js or jQuery, migrate the relevant code to Vue.js.
- **Guard user data inside Vue mounts (template injection)** - The app uses Vue's full build with the runtime template compiler, so any element Vue mounts has its server-rendered HTML compiled as a Vue template. User-controlled data echoed server-side as a text node inside a Vue-mounted element MUST carry `v-pre` (or use the `<x-user-text>` component). The content of a `<textarea>` and the label of an `<option>` are text nodes too, and Vue compiles both: the event form's description, its category and seating plan options and the agenda part names beside fan content were live for that reason until 2026-10 (`EventFormTemplateInjectionTest`). So was the ticket currency in the Payment row's notice, a free string until the form requests began refusing one the pickers do not offer; `<x-input-error>` carries `v-pre` on every message for the same reason. Blade's `{{ }}` HTML-escaping does NOT stop Vue from compiling a Vue mustache expression in the value, so an unguarded value runs as JavaScript (CSP `unsafe-eval` is intentionally on and will not block it). Passing data via `@json()`/props and rendering with Vue's own `@{{ }}` interpolation is safe, and mustaches in HTML attribute values are not compiled.
- **Keep template variable lists in sync** - The same template variables are documented in both `event-graphics.blade.php` and `creating-schedules.blade.php` (integrations Advanced section). When adding or removing variables in `EventTextGenerator::parseTemplate()`, update both doc pages.
- **Use button components in the AP** - Use `<x-brand-button>` for primary actions (save, submit, filter), `<x-secondary-link>` for secondary navigation (back, cancel, clear), and `<x-danger-button>` for destructive actions. `<x-secondary-button>` is only for small utility buttons within forms (validate, view map, edit slug); for standalone action buttons use `<x-secondary-link>` or a plain `<button>` with `<x-secondary-link>` classes (`px-4 py-3 text-base`). Small utility buttons (e.g. "Add 30 days") may use inline styles but must use `focus:ring-[var(--brand-blue)]` for focus rings. The three tabbed forms (the event form, the schedule form, the settings pages in `resources/views/profile/`) all use `<x-brand-button>` for Save and `<x-brand-button size="sm">` for a row's own action: the settings pages used the black `<x-primary-button>` until the 2026-10 redesign brought them into line. Never use inline button styles when a Blade button component exists.

## Terminology

- **WP** - Marketing site (from WordPress acronym)
- **AP** - Admin portal
- **GP** - Guest portal / Client portal
- **Role** (code) = **schedule** (UI) - The `Role` model represents a schedule. Always refer to it as "schedule" in text
- **Group** (code) = **sub-schedule** (UI) - The `Group` model represents a sub-schedule. Always refer to it as "sub-schedule" in text
- **Schedule types** - Only 3 types exist: Talent, Venue, Curator. Never reference "vendor" as a schedule type.

## Brand Colors

- **WP primary blue:** `#4E81FA`
- **WP gradient:** `#4E81FA` -> `#0EA5E9` -> `#22D3EE`
- Shared `.text-gradient` class is defined in `resources/css/marketing.css`
- Never use purple/violet/indigo/fuchsia/pink as WP brand colors
- Icon accent colors (on sub-audience-cards) are decorative and exempt
- **AP brand blue via CSS variables** - In AP/auth views, use CSS variables instead of hardcoded hex: `var(--brand-blue)` (primary), `var(--brand-blue-light)` (lighter), `var(--brand-blue-dark)` (hover/darker). These auto-adapt between light and dark mode. For **text/borders**: `text-[var(--brand-blue)]`, `border-[var(--brand-blue)]`. For **button/element backgrounds**: `bg-[var(--brand-button-bg)]`, `hover:bg-[var(--brand-button-bg-hover)]`. For **button gradients**: `from-[var(--brand-button-bg-light)]`, `to-[var(--brand-button-bg)]`, `hover:from-[var(--brand-button-bg)]`, `hover:to-[var(--brand-button-bg-hover)]`. In inline styles: `color: var(--brand-blue)`, `background-color: var(--brand-button-bg)`. In JS (e.g. Chart.js canvas): `getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim()`. The split exists because dark mode needs brighter blue for text readability but darker blue for button backgrounds (white text contrast). Do NOT add `dark:` variants for brand blue classes - the CSS variable handles dark mode automatically.
- **Never hardcode a surface or ink hex - use the `--ap-*` tokens.** The AP supports six palettes (light: Sand/Mist/Paper, dark: Espresso/Midnight/Carbon), ported from the Flutter client's `InTheme` design system. A literal hex cannot follow the active palette. Use `rgb(var(--ap-surface))`, `rgb(var(--ap-bg))`, `rgb(var(--ap-border))`, `rgb(var(--ap-border-strong))`, `rgb(var(--ap-ink))` / `-ink-2` / `-ink-3` / `-ink-4`, the sidebar's `--ap-rail*` family, `--ap-surface-hover` / `-active`, and the composites `--ap-shadow-*`, `--ap-hairline`, `--ap-tint-1` / `-2`. In Tailwind markup just use the palette classes (`bg-gray-800`, `dark:text-gray-300`, ...) - the whole ramp resolves through those tokens, so it themes automatically. All six palettes are defined in `resources/css/app.css`.
- **The `--ap-*` fallback block is duplicated in `marketing-app.css`** - it is a separate Vite entry that emits its own copy of the Tailwind utilities from the same config. If you add or rename a token, mirror the `:root` / `.dark` fallback there or every `bg-gray-*` on the marketing site resolves to an undefined variable. Marketing, docs and the guest portal deliberately do NOT get the six variants: they set no `data-theme`, so they land on the fallback and render exactly as they always have.
- **Theme variants are opt-in per layout, not per shell.** `layouts/app.blade.php` is the shell for BOTH the admin portal and the guest portal (`app-guest.blade.php` opens with `<x-app-layout>` just like `app-admin.blade.php`). Only `app-admin.blade.php` passes `:theme-variants="true"`, and `layouts/auth.blade.php` opts in directly. Never move that flag into the shell.
- **Do not let `@tailwindcss/forms` own the `<select>` chevron.** The plugin inlines `colors.gray.500` into an SVG *data URI*, which cannot resolve a page-level `var()`, so the arrow vanishes once the ramp is variable-driven. `app.css` re-declares that `background-image` with a literal stroke per palette - keep it in sync if a palette's gray-500 changes.
- **Custom 400 shade overrides** - `tailwind.config.js` overrides the 400 shade for green, red, amber, blue, indigo, and purple to be slightly brighter (better contrast on dark backgrounds). These are used with `dark:` prefixes (e.g. `dark:text-green-400`). No template changes needed - Tailwind uses them automatically.

## AP Design System

The AP uses a refined dark/light design language. Follow these principles when building or modifying AP components:

- **Depth through shades, not borders** - In dark mode, create visual hierarchy using subtle background shade variations (e.g. `#1A1A1A` → `#252526` → `#2d2d30`) and subtle gradients. Avoid relying on bright borders or heavy drop shadows for depth.
- **Inset shadows for active/selected states** - Active or selected items in segmented controls, tabs, and toolbars should use an inset shadow (e.g. `box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5)`) with a slightly different background shade to create a "pressed" feel.
- **Ultra-subtle separators** - Dividers between items in grouped controls should be barely visible: use `w-px` width with very low opacity (e.g. `bg-white/[0.08]` in dark mode, `bg-black/[0.08]` in light mode).
- **Generous rounded corners** - Grouped controls and containers use large border radii (`rounded-xl` to `rounded-2xl`). Individual items within groups use slightly smaller radii (e.g. `rounded-lg` to `rounded-xl`).
- **Subtle shadows** - Use `shadow-sm` for resting states and `shadow-md`/`shadow-lg` on hover. Never use heavy or colored shadows. Dark mode focus ring offset: `dark:focus:ring-offset-gray-800`.
- **Outline-style icons** - Prefer thin stroke/outline icons (not filled) in the AP. Use consistent sizing (`h-5 w-5` or `h-6 w-6`).
- **Smooth transitions** - All interactive elements should use `transition-all duration-200`. Hover effects can include subtle scale (`hover:scale-105`) and shadow changes.
- **Use `ap-card` for AP panels and cards** - All card/panel containers in the AP should use the `ap-card` class with `rounded-xl`. Do not manually add `bg-white`, `shadow-sm`, or `border border-gray-200` - `ap-card` handles light/dark backgrounds, shadows, and the top-edge glow automatically. Use `bg-gray-50`/`bg-gray-100` (`dark:bg-[#252526]`/`dark:bg-[#2d2d30]`) for secondary surfaces and hover states.
- **Consistent panel spacing** - Use `gap-4` for grid gaps between panels/cards and `space-y-4` for vertical spacing between page sections. Do not use `gap-6` or `space-y-6` for panel layouts.
- **Dashboard-style stat panels** - A stat card with an icon is one of two components, never hand-rolled: `<x-dashboard-tile>` on `/dashboard` (a link to the page behind the number: figure and caption, a strip of bars, one label and value under a hairline) and `<x-admin-stat-tile>` on `/admin/dashboard`. Both are an `ap-card rounded-xl` with the `dashboard-icon` class (`p-2 rounded-xl`, a subtle `bg-{color}-50 dark:bg-{color}-500/10` background, a `--icon-glow` CSS variable, a `w-5 h-5` icon) and a `dashboard-stat-value text-3xl font-bold` figure; a new stat card anywhere else in the AP follows the same anatomy. Never override `.ap-card` CSS-driven polish (top-edge gradient glow, inset shadows, icon halos, text shadows) with inline styles. Never use `rounded-full` circles or saturated `bg-{color}-100 dark:bg-{color}-900` backgrounds for panel icons. A partial that a tile or a list includes must not test a flag by a name its parent might also hold (`$live`, `$small`): `@include` hands down every variable of the including view, which is how the first refresh of the Realtime tile redrew the other three tiles' bars (`home/_bars`, `home/_thumb`).

## Build & Development Commands

```bash
# Install dependencies
composer install
npm install

# Build frontend assets
npm run dev       # Development with hot reload
npm run build     # Production build

# Run development server
php artisan serve

# Database
php artisan migrate
php artisan storage:link
```

## Testing

PHPUnit (Feature/Unit) tests are safe to run locally with `php artisan test`, and safe to run in several sessions at once.

`tests/bootstrap.php` runs before Laravel boots and points each run at its own schema:

- **Per session.** `eventschedule_test_<token>`, where the token is the first 8 alphanumeric characters of `CLAUDE_CODE_SESSION_ID`. Set `TEST_DB_TOKEN` to choose one yourself (non-alphanumerics are stripped and it is capped at 16 characters, so `my-feature-one` becomes `myfeatureone`).
- **No token set** (CI, or a plain terminal): the shared `eventschedule_test`, exactly as before. `env -u CLAUDE_CODE_SESSION_ID php artisan test` forces this, which is the escape hatch on a machine where the grant below is not available.
- **Same schema twice at once**: the second run waits on a lock instead of dropping the first run's tables mid-transaction, which used to hang the suite on a `lock_wait_timeout` measured in years.
- **Housekeeping.** Schemas unused for 3 days are dropped on the next run; override with `TEST_DB_PRUNE_DAYS`.

Creating those schemas needs a one-time grant, already applied on this machine. On a new machine, as MySQL root:

```sql
GRANT ALL PRIVILEGES ON `eventschedule\_test\_%`.* TO 'eventschedule'@'localhost';
FLUSH PRIVILEGES;
```

The `\_` escapes are load-bearing: they make the underscores literal, so the pattern can only ever match `eventschedule_test_<something>`.

**Dusk browser tests wipe the database `.env.dusk.local` points at** - which is now
`eventschedule_test_dusk`, its own schema, matched by the same `eventschedule\_test\_%` grant the
PHPUnit schemas use. It used to point at `eventschedule`, the dev database, which is why running
Dusk locally was forbidden.

Dusk needs the app served at the `APP_URL` in that file, so start `php artisan serve` (port 8000)
first. It is otherwise unaffected by the per-session PHPUnit schemas above.

```bash
# Run Feature & Unit tests (safe locally)
php artisan test

# Run all browser tests (CI only - see warning above)
php artisan dusk

# Run specific test file
php artisan dusk tests/Browser/GeneralTest.php

# Test setup (first time only)
php artisan dusk:install
php artisan dusk:chrome-driver
cp .env .env.dusk.local
```

Test files: `tests/Browser/GeneralTest.php`, `TicketTest.php`, `CuratorEventTest.php`, `ApiTest.php`, `GroupsTest.php`

## Code Quality

```bash
# PHP code style (Laravel Pint)
./vendor/bin/pint

# Check for security vulnerabilities
composer audit
```

## Release Notes

When the user asks for "release notes" (or "releasenotes"), generate the notes for the **next**
version of the app and print the markdown in chat. Do not create a GitHub release/tag and do not
bump version files unless explicitly asked separately. Follow the established style at
https://github.com/eventschedule/eventschedule/releases.

**Steps:**

1. **Find the last release and next version.** The authoritative last published release is
   `gh release view --json tagName,name` (cross-check `version_installed` in
   `config/self-update.php`). Versions are `vMAJOR.MINOR.PATCH` (e.g. `v1.0.111`). The next
   version is a **patch bump** by default (`v1.0.111` -> `v1.0.112`); only use a minor/major
   bump if the user asks.

2. **Review changes since the last release.** Run `git log <last-release-tag>..HEAD --oneline`.
   If the tag is missing locally (local tags can lag GitHub), run `git fetch --tags` first. Read
   the actual commits closely enough to describe each change accurately; for a referenced issue/PR
   you can read it with `gh issue view <n>` / `gh pr view <n>` for a clearer summary.

3. **Write short, user-facing bullets** matching the house style:
   - Bullet list only, each prefixed with `Added:`, `Updated:`, or `Fixed:`. No emoji.
   - Keep it short and sweet (past releases are ~1-7 bullets). Describe user-facing impact,
     not implementation details.
   - Skip internal-only commits (test-only changes, version bumps, CI, no-op refactors).
   - Merge related commits into a single bullet.
   - When a commit references an issue/PR number (e.g. `#89`), link it inline:
     `[#89](https://github.com/eventschedule/eventschedule/issues/89)`.

4. **Link features to the user guide (not fixes).** Every `Added:` and `Updated:` bullet must
   include a user-guide link - the user guide is the public docs at
   `https://eventschedule.com/docs/{slug}#{anchor}`. Do NOT add user-guide links to `Fixed:`
   bullets: the docs describe features, not bug fixes, so a fix has no matching section. (Inline
   issue/PR links like `[#90](...)` are still fine on fixes.) Link each feature to its relevant
   section (e.g. a trailing `[Learn more](...)` or by hyperlinking the feature name): find the page
   slug from the `marketing.docs.*` routes in `routes/web.php`, and the section anchor from
   `MarketingController::getDocSearchIndex()` or the feature->anchor map in `app/Utils/HelpUtils.php`.
   Take slugs/anchors from those sources - never invent an anchor; if no exact section fits, link the
   closest page (or the docs home, `https://eventschedule.com/docs`). Never ship a feature bullet
   without a user-guide link.

5. **Output.** Print the version as the title followed by the bullet body, as markdown in chat,
   ready to paste into GitHub's release form.

Apply the repo's writing rules to the notes too: no em-dashes; "schedule" not "role"; "selfhost"
not "self-host".

**Example output:**

```
v1.0.112

- Added: OneSignal web push notifications so guests can opt in to event reminders. [Learn more](https://eventschedule.com/docs/account-settings)
- Updated: Custom dashboard links [#87](https://github.com/eventschedule/eventschedule/issues/87) [Learn more](https://eventschedule.com/docs/getting-started)
- Fixed: Markdown not formatting correctly in some event descriptions [#90](https://github.com/eventschedule/eventschedule/issues/90)
```

## Growth Data

When the user asks how to grow, what to build next, or how the funnel, conversion, retention, MRR
or sellers are doing, use the `growth-review` skill (`.claude/skills/growth-review/SKILL.md`).

- `php artisan app:pull-growth` downloads the pseudonymous growth payload from the hosted install
  into `storage/app/growth/` (`latest.json`, plus a timestamped copy per pull) and prints the
  headline numbers against the previous pull. It needs `GROWTH_DATA_TOKEN` in `.env`, the same value
  as on the server. `--local` re-prints the summary without downloading.
- `docs/GROWTH_DATA.md` defines every field, population and window, and lists the caveats. Read it
  before drawing conclusions. Query the JSON with `jq` or `python3`; it is too big to Read.
- **Never commit a pull or any figure from one.** The repository is public. Conclusions and
  hypotheses go to memory (`hub_growth_funnel`, `project_growth_experiments_ledger`).
- **Changing `GrowthExportService`'s output** means bumping `SCHEMA_VERSION` and updating
  `docs/GROWTH_DATA.md` (`GrowthDataDictionaryTest` enforces both). Anything visitor-controlled must
  pass the same anonymisation (`GrowthDataEndpointTest` holds the sentinel list).

## Feature Tiers (Free / Pro / Enterprise)

See `docs/FEATURES.md` for the complete reference of which features belong to each plan tier. **Always consult `docs/FEATURES.md`** when:
- Updating the pricing page, comparison/alternative pages, or feature marketing pages
- Updating the user guide or documentation
- Adding or modifying gate checks (`$role->isPro()`, `$role->isEnterprise()`) in the AP
- Writing feature descriptions that mention plan availability

## Architecture

### Multi-Tenant Routing
- **Hosted mode** (`IS_HOSTED=true`): Uses subdomains (`{subdomain}.eventschedule.com`)
- **Selfhosted mode** (`IS_HOSTED=false`): Uses path-based routing (`/{subdomain}/...`)

Routes are defined conditionally in `routes/web.php` based on `config('app.hosted')`.

### Key Directories
- `app/Services/` - Business logic (GoogleCalendarService, EmailService, EventGraphicGenerator)
- `app/Jobs/` - Background jobs for async operations (Google Calendar sync)
- `app/Utils/` - Helper utilities (MarkdownUtils, MoneyUtils)
- `app/Repos/` - Data repositories

### Core Models
- `User` - Authentication (supports Google/Facebook OAuth via Socialite)
- `Role` - Represents a **schedule** (called `Role` in code). The tenant in multi-tenant
- `Event` - Event details with markdown descriptions
- `Ticket` - Ticket types for events
- `Sale` - Purchase records with payment tracking
- `Group` - Represents a **sub-schedule** (called `Group` in code). Event categories within a schedule

### Frontend
- Use Vue.js for JavaScript functionality

### Important Integrations
- **Payments**: Stripe direct integration + Invoice Ninja
- **Google Calendar**: Bidirectional sync with webhook support (`app/Services/GoogleCalendarService.php`)
- **AI Features**: Google Gemini for event parsing and translation (`GEMINI_API_KEY`)

### Security
- CSP nonces for inline scripts: use `{!! nonce_attr() !!}` or `nonce="{{ csp_nonce() }}"`
- HTML Purifier for markdown content (XSS prevention)
- Environment-aware security headers in `app/Http/Middleware/SecurityHeaders.php`
- **Always encode IDs visible to users** - Use `UrlUtils::encodeId()` for IDs in URLs, and `UrlUtils::decodeId()` in controllers to decode them

### Scheduled Tasks

There are two interchangeable rails, and `CLAUDE.md`'s sync rule above exists because both must
list every command (`php artisan schedule:list` prints the scheduler rail):

- **`routes/console.php`** - the Laravel scheduler. Selfhost drives it with the documented crontab
  entry `* * * * * php artisan schedule:run`; hosted drives it with `php artisan schedule:work` on
  a DigitalOcean App Platform worker. See `docs/DIGITALOCEAN_WORKER.md`.
- **`AppController::translateData()`** - `GET /translate_data?secret=$APP_CRON_SECRET`, a complete
  second copy of the schedule as cache-key-gated tiers. For installs that cannot run a cron
  process, and as the hosted emergency fallback. Unsetting `APP_CRON_SECRET` disables it (and
  `/release_tickets`).

The queue is drained by the `process-queue` entry inside the schedule, not by a resident worker, so
dispatch latency is up to about a minute. Both rails stamp `scheduler.last_run_at` every tick;
`AdminAlertService`'s `scheduler_stalled` row alerts when that goes stale.

**Deploying the hosted install is documented in `docs/NEXUS_RELEASE.md`.** The deploy is push to
`main`, then click Deploy in the DigitalOcean console - there is deliberately no maintenance
command to run, because the production database is not reachable from a dev machine and a
command that ships in the release cannot check the release before it deploys. Anything needing
production data is surfaced by the app instead: `AdminAlertService` on `/admin`, the Scheduler
card on `/admin/queue`, and `/up`, whose `DiagnosingHealth` listener round-trips the database
and the cache store. Production config for hosted is the DigitalOcean app spec, not any `.env`.

**Anonymous marketing HTML is edge-cached** - `CacheableMarketingResponse` strips the session and
CSRF cookies and sets `s-maxage=600` on cookie-free anonymous `marketing.*` GETs, so page views are
counted by a `sendBeacon` and first-touch attribution by a browser-written `es_attribution` cookie
rather than by the session. Read `docs/CACHING.md` before touching either, or before adding a
marketing page that renders anything visitor-specific.

**Hosted (eventschedule.com) runs `QUEUE_CONNECTION=database` and `CACHE_STORE=database`** (set on
2026-09-06, before the scheduler worker became a second container). Every scheduler mutex and every
cross-rail lock lives in the cache, so it must stay a store all containers share: on `file`, two
containers serialise against nothing. Laravel's database store never deletes an expired row unless
that key is read again, so `app:prune-cache` (hourly, both rails) is what keeps the `cache` table
bounded. Without it the per-visitor daily keys pile up for good, which is what turned up when the
1 GB MySQL started alerting on memory in October 2026.

## Environment Variables

Key configuration in `.env`:
- `IS_HOSTED` - `true` for SaaS, `false` for selfhosted
- `APP_TESTING` - Set to `true` in test environment
- `GEMINI_API_KEY` - For AI event parsing/translation
- `REPORT_ERRORS` - Enable Sentry error reporting

## Localization

Supported languages are defined in `config('app.supported_languages')`. Each has a corresponding directory in `resources/lang/`.

```bash
# Check for missing translation keys across all languages
php storage/check_translations.php
```

Run this periodically when adding new translation keys to ensure all language files are in sync.
