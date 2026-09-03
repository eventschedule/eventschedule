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
- **Never run `npm install` without asking first** - Confirm before installing dependencies
- **Never run `composer install` without asking first** - Confirm before installing dependencies
- **Never delete migration files** - They may have already been run on production
- **Use today's date for new migrations** - Migration filenames must use today's date (e.g. `2026_04_15_000000_`), never a future or past date
- **Use "selfhost" not "self-host"** - Always write "selfhost" and "selfhosted" (no hyphen) except for "self-hosting"
- **Keep the sitemap up-to-date** - When adding new pages, add them to `resources/views/sitemap.blade.php`. Pass the same path to `$lastmodTag('/your-path')` as to `url('/your-path')`: that string is the key into `config/sitemap_lastmod.php`, so a mismatch silently costs the page its `<lastmod>`. `tests/Feature/SitemapCoverageTest.php` fails the build if a `marketing.*` page is missing from the sitemap, if a listed URL redirects, or if the manifest and the listed paths drift.
- **Run `php artisan sitemap:lastmod` before a release** - It rebuilds `config/sitemap_lastmod.php` (URL path to git commit date) so `/sitemap-pages.xml` carries a real per-page `<lastmod>`; commit the result. The deployed container has neither usable file mtimes nor a git history, so an unrefreshed manifest just means slightly stale dates - which is still far better than the shared boot timestamp every page used to report.
- **Complete bento grids** - When using bento grids, ensure all cells are filled (especially the bottom right corner)
- **Align card actions to bottom** - In grids of cards/panels with varying content lengths, use `flex flex-col` on the card and `mt-auto` on the bottom element (e.g. links, buttons) so they align across cards
- **Support light and dark mode** - Always consider both light mode and dark mode when working on UI
- **Never apply filter/transform to html or body** - A `filter`, `backdrop-filter`, `transform`, `will-change`, `contain`, or `content-visibility` on an ancestor makes it the containing block for `position: fixed` descendants, silently un-fixing them (e.g. the GP mobile CTA bar drops to the bottom of the document). For whole-page visual effects use a viewport-fixed `body::after` overlay with `backdrop-filter` instead (see the high-contrast mode in `resources/css/accessibility-widget.css`).
- **Forward button at the end** - In button pairs (e.g. cancel/submit), place the forward action button at the end (right in LTR, left in RTL)
- **Work directly on `main`** - Do not create feature branches; commit all changes directly to the `main` branch
- **No co-author on commits** - Do not add "Co-Authored-By: Claude" to git commit messages
- **Never use em-dashes** - Use hyphens, "to", or "or" instead of em-dashes (—) in all written content
- **Use "schedule" not "role", "sub-schedule" not "group"** - In the code, `Role` = schedule and `Group` = sub-schedule. Always use "schedule" and "sub-schedule" in UI text and conversations, never "role" or "group"
- **MySQL only** - Only MySQL is supported; do not add SQLite compatibility to migrations or tests
- **Never use CDNs** - Always use local vendor files for JS/CSS libraries. Selfhosted users should not have the app calling external servers.
- **Never add npm dependencies** - Do not use `npm install` to add new packages. Instead, download built files manually and place them in `public/vendor/`.
- **Use `<x-link>` for inline text links** - Always use the `<x-link>` Blade component for inline text links (not navigation or buttons). It provides consistent styling, dark mode support, and an external link icon for `target="_blank"` links.
- **Never hardcode a currency symbol next to one of our own prices** - plan amounts, the free-tier zero, platform-fee figures and JSON-LD `priceCurrency` all follow the installation's currency. Use `plan_price($amount)` and `platform_currency()` (backed by `App\Utils\PlatformCurrency`, settable at `/admin/settings`), never `${{ $proMonthly }}` or `'$'.$amount`. `tests/Feature/MarketingPriceTest.php` fails the build if one creeps back. Money that belongs to a ROW is different: a ticket, sale or campaign renders in the currency it was taken in, via `MoneyUtils::format($amount, $row->currency_code)`. Amounts that are factually someone else's USD (Stripe's `$0.30`, competitor pricing, the fee calculators) stay hardcoded.
- **Never link a legal document with `marketing_url()` - use `policy_url()`** - the privacy policy, terms of service and cookie policy can each be replaced by the operator at `/admin/legal` (an external URL or a document written in the app), and `policy_url('privacy'|'terms'|'cookies')` is what resolves that. `marketing_url('/privacy')` hardcodes eventschedule.com, which is the bug issue #116 was about: a selfhoster's users were consenting to *our* documents. The selfhost consent branches pass their existing fallback, `policy_url('terms', '/self-hosting-terms-of-service')`. `tests/Feature/PolicyLinkTest.php` fails the build if one creeps back. The four bundled marketing pages (`marketing/privacy.blade.php` and siblings) are the allow-listed exception.
- **Event dates render in the SCHEDULE's timezone, never the viewer's** - `Event::getStartDateTime()` defaults to `scheduleTimezone()` (`creatorRole?->timezone ?: config('app.timezone')`); a viewer's `users.timezone` is reachable only through an explicit `$timezoneOverride`, which exists to show a guest their own local time (`AppointmentTimeUtils`). An occurrence falls on a given day because of where it happens, not who is looking. Anything deciding which DAY a date belongs to - day bucketing (`matchesDate()`), the Vue past-event filters and `userTimezone` in `role/partials/calendar.blade.php`, a "today" highlight - must resolve the SAME zone as the dates it compares, or an event is shown on one day and filtered out as though it were on another. A narrowed `get(['events.id', ...])` must include `events.creator_role_id`, or `BelongsTo` short-circuits on the null key and the zone silently falls back to the app timezone with no query and no error. `tests/Unit/EventVenueTimeRenderingTest.php` and `tests/Feature/CalendarTimezoneTest.php` fail the build if one creeps back.
- **Use `config('app.supported_languages')` for language lists** - Never hardcode language code arrays. Always reference the centralized list in `config/app.php`.
- **Keep Help button mappings up-to-date** - When adding, removing, or moving doc pages, update the anchor map in `app/Utils/HelpUtils.php` so the admin panel Help button links to the correct docs for each section/tab
- **Match docs structure to app layout** - Documentation sections and sub-sections should mirror the app's UI structure (sections, tabs, sidebar items) where it makes sense. This keeps the Help button deep links aligned and makes docs intuitive for users navigating between the app and docs.
- **Keep `translateData` and `console.php` in sync** - Scheduled commands must be registered in both `AppController::translateData()` (the HTTP cron rail) and `routes/console.php` (the scheduler rail). Add it to both places with matching frequency, and with the SAME `config('app.hosted')` gate: a gate on one rail only means an install using the other rail runs a command it should not (that is how selfhost installs on `/translate_data` ended up mailing onboarding nudges). `tests/Feature/CronRailSyncTest.php` fails the build on presence, frequency, argument or gate-polarity drift (a deliberate cadence difference goes in its `CADENCE_EXCEPTIONS` list with the reasoning). Read its class docblock before trusting a green run - it lists the shapes it cannot see, such as a tier whose TTL changed and gates written with `->when()` instead of `if`.
- **Every scheduler entry needs `->name()` and a bounded `->withoutOverlapping(N)`** - `CallbackEvent::withoutOverlapping()` throws without a prior `->name()`, so an unnamed entry has no overlap protection at all and logs as `Running [Callback]`; and its default expiry is 1440 MINUTES, released in a `finally` that a SIGKILL skips - so a container killed mid-run strands the mutex for a full day. `schedule:work` starts a new `schedule:run` every minute without waiting for the last one, so overlap is normal. Size N just above the entry's own budget. `tests/Feature/SchedulerHealthTest.php` fails the build otherwise.
- **Use toggle switches for boolean settings** - In the admin portal, use `<x-toggle>` (or toggle switch markup for Vue pages) for standalone boolean on/off settings. Reserve plain checkboxes for multi-select lists and "required" indicators.
- **Consistent primary action button sizing** - Primary action buttons in the AP should use `px-4 py-3 text-base` sizing to match `<x-brand-link>` / `<x-secondary-link>` components. Do not use smaller `py-2 text-sm` for standalone call-to-action buttons.
- **Keep doc search index up-to-date** - When adding, removing, or renaming doc sections, update `getDocSearchIndex()` in `MarketingController` so the docs search stays accurate
- **Follower emails are visible on all schedule-owner-facing surfaces** - Schedule owners can see their followers' name and email on the followers tab (`show-admin-followers.blade.php`), the newsletter stats and segment-edit pages, and the dashboard recent-activity feed. Follower emails must NEVER appear on public/guest-facing surfaces (public stats, embed widgets, guest pages) - those do not list individual followers. When a user clicks Follow on the guest portal, a consent modal (`resources/views/partials/follow-consent-modal.blade.php`) discloses that the schedule will see their name and email.
- **Every public form needs a honeypot** - Any form a signed-out visitor can submit gets `<x-honeypot />` plus an `App\Utils\HoneypotUtils::isTripped($request)` check in the controller. Match the bail to what the surface renders: guest pages show `session('error')`, so use `back()->withInput()->with('error', __('messages.invalid_request'))`; `x-auth-layout` pages render only per-field errors, so those must `throw ValidationException::withMessages([...])` or the rejection is invisible. For forms whose payload is built by hand in JS, pass `vmodel` and send the value explicitly - the hidden input alone is not submitted. Never add it to a form that posts a genuine `website` value (the schedule edit form, the schedules API), and never to an authenticated-only form, where a password manager could fill it. Turnstile is not a substitute: it is off whenever `TURNSTILE_*` is unset (every selfhost install) and on custom domains.
- **Never expose raw exception messages to users** - In catch blocks that handle user-facing responses, catch `QueryException` (and other system exceptions) separately and show a generic error message. Use `report($e)` to send to Sentry. Only show `$e->getMessage()` for intentional business logic exceptions.
- **Always translate new language keys** - When adding a key to non-English `resources/lang/` files, use proper translations (check existing similar keys in the file for reference), never copy the English string
- **Use bordered panels for AP warnings** - Never use plain colored text for warnings. Use `bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3` with a warning triangle SVG icon (`w-5 h-5 text-amber-600 dark:text-amber-400`) inside a flex layout
- **Never modify WP pricing feature lists or header/footer links** - The feature lists on the /pricing page and the WP header/footer navigation links are manually curated. Do not add, remove, or reorder items unless explicitly asked.
- **Never add decorative line drawings to the WP** - Do not add outline SVG illustrations of objects or scenes (building facades, floor plans, mic stands, trapeze rigs, velvet ropes, bunting, rows of repeating outline glyphs) as ornament behind headlines or inside panels on the marketing site. WP depth comes from gradient auroras, grid overlays, noise texture and light beams. Functional icons, brand logos, product screenshots, `<pattern>` textures and abstract strokes (route lines, connector curves, hand-drawn heading underlines) are fine.
- **Use Flatpickr for date inputs** - Always use Flatpickr (already bundled via `app.js`) instead of native `type="date"` inputs. Use `dateFormat: "Y-m-d"` with `altInput: true` and `altFormat: "M j, Y"` for a human-readable display.
- **Use Vue.js, not Alpine.js or jQuery** - Always use Vue.js for JavaScript interactivity. Do not use Alpine.js directives (x-data, x-show, @click, etc.) or jQuery. When modifying files that use Alpine.js or jQuery, migrate the relevant code to Vue.js.
- **Guard user data inside Vue mounts (template injection)** - The app uses Vue's full build with the runtime template compiler, so any element Vue mounts has its server-rendered HTML compiled as a Vue template. User-controlled data echoed server-side as a text node inside a Vue-mounted element MUST carry `v-pre` (or use the `<x-user-text>` component). Blade's `{{ }}` HTML-escaping does NOT stop Vue from compiling a Vue mustache expression in the value, so an unguarded value runs as JavaScript (CSP `unsafe-eval` is intentionally on and will not block it). Passing data via `@json()`/props and rendering with Vue's own `@{{ }}` interpolation is safe, and mustaches in HTML attribute values are not compiled.
- **Keep template variable lists in sync** - The same template variables are documented in both `event-graphics.blade.php` and `creating-schedules.blade.php` (integrations Advanced section). When adding or removing variables in `EventTextGenerator::parseTemplate()`, update both doc pages.
- **Use button components in the AP** - Use `<x-brand-button>` for primary actions (save, submit, filter), `<x-secondary-link>` for secondary navigation (back, cancel, clear), and `<x-danger-button>` for destructive actions. `<x-secondary-button>` is only for small utility buttons within forms (validate, view map, edit slug); for standalone action buttons use `<x-secondary-link>` or a plain `<button>` with `<x-secondary-link>` classes (`px-4 py-3 text-base`). Small utility buttons (e.g. "Add 30 days") may use inline styles but must use `focus:ring-[var(--brand-blue)]` for focus rings. Settings pages (`resources/views/profile/`) use `<x-primary-button>` instead of `<x-brand-button>`. Never use inline button styles when a Blade button component exists.

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
- **Dashboard-style stat panels** - For stat cards with icons, use the `dashboard-icon` class with `p-2 rounded-xl`, subtle backgrounds (`bg-{color}-50 dark:bg-{color}-500/10`), a `--icon-glow` CSS variable, and `w-5 h-5` icons. The full panel structure: `ap-card rounded-xl p-6 flex flex-col items-center` on the card, icon+label row with `flex items-center gap-3 mb-3 self-start`, stat value with `dashboard-stat-value text-3xl font-bold text-center`. Extra content below (change %, "in period") gets `w-full`. Never override `.ap-card` CSS-driven polish (top-edge gradient glow, inset shadows, icon halos, text shadows) with inline styles. Never use `rounded-full` circles or saturated `bg-{color}-100 dark:bg-{color}-900` backgrounds for panel icons.

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

**Hosted (eventschedule.com) runs `QUEUE_CONNECTION=database`, and `CACHE_STORE` must be set to
`database` before a second container exists** - it is currently unset there, which resolves to the
`file` driver, and that is safe only while one container serves everything. Every scheduler mutex
and every cross-rail lock lives in the cache, so on `file` two containers serialise against
nothing. Setting it is a step in `docs/DIGITALOCEAN_WORKER.md`; restate it as fact once done.

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
