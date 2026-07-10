# Event Schedule Refactoring Playbook

**Audience:** a Claude (Opus) session executing one phase per session.
**Prime directive:** after every phase, the app behaves EXACTLY as it did before - bug-for-bug. This campaign changes structure, never behavior.
**Survey baseline:** all line numbers and counts in this document were verified against the tree on 2026-07-10 (around commit `e4cae0d9`). Line numbers are anchors, not truth - always re-locate code by symbol name and content before cutting. If the tree contradicts this document, trust the tree, note the discrepancy in the Campaign Log (Appendix E), and proceed conservatively.

---

## 1. How to use this document

The owner starts a session with a prompt like:

> Read REFACTOR_PLAN.md. Execute Phase P3. Follow the campaign rules and verification playbook exactly.

When you receive that prompt:

1. Read Sections 2 (Campaign rules), 4 (Known landmines), and 5 (Semantic-preservation rules) in full. Read Section 3 (Verification playbook) for the artifact types your phase touches. Read your phase's work order in Section 6 in full.
2. Check the Campaign Log (Appendix E): confirm every phase your phase depends on is marked done. If a dependency is missing, stop and tell the owner.
3. Confirm preconditions: `git status --porcelain` is empty, and the full test suite is green (`php artisan test` - safe only after P0 is done; before P0 rely on CI).
4. Create the phase branch: `git checkout -b refactor/p03-designs-dedup` (pattern: `refactor/<phase-id-lowercase>-<slug>`).
5. Execute the work order: characterization tests first (committed separately), then the refactor, then the postflight checklist (Section 3.8).
6. Update the Campaign Log row for your phase and present the phase's manual-QA checklist to the owner.

**Rules of engagement:**

- **One phase per session.** Never combine phases. If a large phase does not finish in one session, commit clean progress, record exact state in the Campaign Log, and stop - the next session resumes it.
- **A phase is done** only when every gate in the postflight checklist passes, CI is green on the pushed branch, and the owner has the manual-QA list.
- **If you cannot reach green by session end, revert.** Never leave the branch red.
- **When in doubt, do less.** Skipping a risky sub-step and documenting it beats an unverifiable "improvement". This document marks several things NOT to touch - respect them.

**Prerequisites before Phase P0 (owner tasks):**

- Commit or stash all current WIP - the campaign always starts from a clean tree.
- Confirm CI is green on `main`.
- Be available to approve: the `eventschedule_test` database creation (P0) and `composer require --dev larastan/larastan` (P1).

---

## 2. Campaign rules (the contract)

Numbered so work orders can cite them.

**R1 - Behavior preservation.** No UI text changes, no route-surface changes (only the `action` column of `route:list` may change, and only in split phases), no schema changes, no new migrations, no dependency additions/removals/upgrades without asking the owner, HTML/JSON output byte-identical modulo CSRF token and CSP nonce. Timing, redirects, status codes, validation error keys, session flash keys, queued jobs, and sent mail are all behavior.

**R2 - Tests before refactor.** Characterization tests are written against the UNMODIFIED code, run green, and committed on their own commit (`test: characterize <target> before refactor`) before any refactor commit. A refactor commit that adds behavior-relevant test changes is a red flag - tests may only be extended, never weakened, during a refactor.

**R3 - Preserve tokens, not intent.** When moving code, keep `request()` vs `$request`, `==` vs `===`, `auth()->user()` vs `Auth::user()`, `has()` vs `filled()`, quote styles inside SQL strings - exactly as written. Several apparent inconsistencies are load-bearing (see Landmine L1). Formatting cleanup happens only via `pint --dirty` and you re-review its diff.

**R4 - Found-a-bug protocol.** Characterization WILL surface latent bugs. When it does:
1. Do NOT fix it. Do NOT clean it up "in passing".
2. Write the characterization test to assert the CURRENT buggy behavior, annotated: `// BUG-NNN (see BUGS_FOUND.md): intentionally asserting current buggy behavior`.
3. Append an entry to `BUGS_FOUND.md` (repo root, committed - template in Appendix B).
4. Complete the refactor preserving the bug bit-for-bit.
5. Bug fixes happen only outside refactor phases, as owner-approved standalone commits that flip the pinned test to the correct expectation in the same commit as the fix.

**Exception - security vulnerabilities:** stop, report privately to the owner immediately, and do NOT pin them in a committed test containing a repro recipe. The owner decides handling.

**R5 - Git discipline.** One branch per phase. Small commits, each green. Move commits contain ONLY moves (verify with `git diff --color-moved=dimmed-zebra` - moved blocks must show as moves, not edits). No `Co-Authored-By` lines (CLAUDE.md rule). Never force-push over the owner's work.

**R6 - Style discipline.** `vendor/bin/pint --dirty` before each commit (touched files only). NEVER run bare `vendor/bin/pint` repo-wide: the repo is not pint-clean, and a sweep would destroy `git blame` and make phase diffs unreviewable - the property this whole campaign depends on.

**R7 - CLAUDE.md references are code.** If a phase moves or renames a symbol that CLAUDE.md names (e.g. `MarketingController::getDocSearchIndex()`, `app/Utils/HelpUtils.php`), update CLAUDE.md in the same commit. Same for `FEATURES.md` and doc pages if they name code paths.

**R8 - Ask before installing.** `composer require`, `npm install`, and new MySQL databases always require explicit owner approval first (CLAUDE.md rules). No CDNs, no new npm packages ever - vendored files only.

**R9 - Scope hygiene.** A phase touches only the files its work order names (plus tests, plus this file's Campaign Log). If you find yourself editing an unrelated file, stop - it belongs to another phase or to the Do-NOT list (Section 6.4).

---

## 3. Verification playbook

### 3.1 Shared infrastructure: `storage/refactor/`

Built in P1, following the repo's dev-script convention (`storage/check_translations.php`). Layout:

```
storage/refactor/
  .gitignore              # contains two lines: baselines/ and current/
  html_snapshot.php       # committed - Section 3.3
  graphic_baseline.php    # committed - Section 3.4
  routes_snapshot.sh      # committed - Section 3.6
  js_smoke.sh             # committed - Section 3.5
  urls.php                # committed URL manifest for html_snapshot.php
  baselines/              # gitignored - regenerated each phase
  current/                # gitignored - regenerated each phase
```

Baselines are gitignored on purpose: they are machine-specific (APP_KEY-derived encoded IDs, GD/freetype build differences) and are regenerated at the START of each phase from the clean pre-phase commit. The workflow is the source of truth, not the artifact. Baseline workflow for every snapshot type:

1. Phase start, clean tree: record `git rev-parse HEAD`, generate baselines into `storage/refactor/baselines/`.
2. Refactor.
3. Generate the same snapshots into `storage/refactor/current/`.
4. `diff -ru storage/refactor/baselines/<type> storage/refactor/current/<type>` must be empty (except where the work order says otherwise, e.g. the action column in split phases).
5. If a baseline is lost or suspect mid-phase: `git stash push` -> regenerate baseline -> `git stash pop`.
6. Delete both dirs at phase end; never carry baselines across phases.

### 3.2 HTTP / controller characterization

**Location and conventions:** `tests/Feature/Characterization/{Target}CharacterizationTest.php`, `extends Tests\TestCase`, `use RefreshDatabase;` and `use Tests\Feature\Concerns\CreatesScheduleData;` - identical to the existing suites so they run inside `php artisan test` with zero wiring. Build data with the trait helpers (`createOwner`, `createRole`, `createEvent`, `createTicket`, `createSale`), drive over HTTP with `route()` names, encode route IDs with `App\Utils\UrlUtils::encodeId()`.

**Capture per endpoint - the full checklist:**

- Exact status code (`assertStatus` - pin 302 vs 303, 200 vs 201) and exact redirect target (`assertRedirect(route(...))`).
- Session flash keys and validation error keys (`assertSessionHas`, `assertSessionHasErrors(['field_a','field_b'])`), and for AJAX callers the 422 JSON shape.
- JSON bodies: `assertExactJson` where feasible, else `assertJsonPath` on every key a consumer reads.
- DB side effects: `assertDatabaseHas`/`assertDatabaseMissing` with FULL column maps including nullables - pin `NULL` vs `''` vs `0` distinctions explicitly; these are exactly what decompositions break.
- Outbound effects: `Mail::fake()` + `assertSent`/`assertQueued` with recipient and mailable class; `Queue::fake()`/`Bus::fake()` + `assertPushed`; `Notification::fake()`; `Storage::fake()` for uploads.
- Download endpoints (ICS, QR, graphics, CSV): content-type and content-disposition headers.
- Never `withoutMiddleware()` - middleware is part of the frozen behavior. Freeze time with `Carbon::setTestNow()` wherever `now()` influences output (reset in `tearDown`).

**Factories:** do NOT build factories for the 55 factory-less models. Extend `tests/Feature/Concerns/CreatesScheduleData.php` with only what phases need: `createGroup()` (sub-schedule), `createCurator()` plus a venue-connection helper, a venue-with-address variant of `createRole()`, `createRecurringEvent()`. A second construction convention would fork the suite.

### 3.3 Rendered-HTML equivalence

Used whenever Blade output must be proven unchanged (P2 spot checks, F5, O1, and any view-touching phase).

**`storage/refactor/html_snapshot.php` design (build in P1):**

- Boots the app and dispatches through the HTTP kernel IN-PROCESS - no live server: `$app = require 'bootstrap/app.php'; $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class); $response = $kernel->handle(Illuminate\Http\Request::create($url, 'GET'));`. CLI dispatch sidesteps the opcache landmine (L7) and allows time freezing.
- BEFORE bootstrap: `putenv('SESSION_DRIVER=array'); putenv('CACHE_STORE=array'); putenv('MAIL_MAILER=array');` - the dev `.env` uses database sessions, and real env vars beat `.env`, so this prevents snapshot runs from writing session rows anywhere.
- After bootstrap: `Carbon::setTestNow('2026-07-15 12:00:00')` (any fixed instant) so `now()`-derived output is frozen.
- URLs are absolute with the local host so host/path routing matches: `Request::create('https://eventschedule.test/for-musicians')`. The URL manifest lives in `storage/refactor/urls.php`: all 34 `/for-*` URIs (derive from `ls resources/views/marketing/for-*.blade.php`), plus per-phase additions (guest schedule/event pages built from known fixture rows).
- Writes `{slug}.html` (normalized body) plus `manifest.txt` lines of `status<TAB>sha256<TAB>slug`.
- Refuses to run if `bootstrap/cache/config.php` exists (cached config would silently override the putenv values).

**Normalization - exactly two rules, nothing else:**

- `<meta name="csrf-token" content="...">` and hidden `_token` inputs -> `__CSRF__`
- `nonce="..."` -> `nonce="__NONCE__"`

Everything else is held constant by pinning inputs: same DB rows, same APP_KEY (encoded IDs are APP_KEY-derived), frozen clock, and no `npm run build` between baseline and compare (Vite hashes must match). If a page still varies run-to-run, add ONE targeted regex and document it in the script header with the reason.

**DB inputs:** the for-* marketing pages are DB-independent. Guest pages need fixture rows: run against the test DB (`DB_DATABASE=eventschedule_test php storage/refactor/html_snapshot.php ...`) after seeding fixed rows there; the script refuses to seed unless the connected DB name ends in `_test`.

**Pass criterion:** zero-byte `diff -ru` after normalization, including the status manifest.

### 3.4 Generated images (GD pipeline)

**Byte-identical PNG comparison is the pass criterion.** This was verified against the code: no `rand()`/`mt_rand`/`uniqid`/`Str::random`/`now()`/`time()`/`date()` anywhere in `app/Services/AbstractEventDesign.php` or `app/Services/designs/*`; the only Carbon usage formats the event's own dates. Output is `imagepng($im, null, 9, PNG_ALL_FILTERS)` (no timestamp chunk); fonts are repo-local (`resources/fonts/Noto*`); images resolve from local disk first. Same machine + same pixels = same bytes.

**`storage/refactor/graphic_baseline.php` design (build in P1):**

- Builds IN-MEMORY `Role`/`Event` models - no DB (`new Event`, `setRelation('venue', ...)`, absolute `starts_at` dates, local fixture flyer/header images to avoid any network fetch).
- Iterates a matrix: 3 layouts x {1, 3, 8, 20} events x {`en`, `he` (RTL)} x {overlay text on/off} x {header image on/off} x {directRegistration true/false}, calling `EventGraphicGenerator` (see `app/Http/Controllers/GraphicController.php` for the current constructor signature) -> writes `NNN_desc.png` and appends to `manifest.sha256`.
- Diff: `diff storage/refactor/baselines/graphics/manifest.sha256 storage/refactor/current/graphics/manifest.sha256`.
- Grows a `--diff a.png b.png` triage mode using only ext-gd: `getimagesize` both, exact per-pixel `imagecolorat` loop reporting mismatch count and first coordinates, and an 8x8 average-hash for "wildly different vs antialiasing" triage.

**Rules:** in P3 (hoisting copy-pasted helpers verbatim), ANY pixel diff means the hoist changed behavior - fix the refactor, never relax to perceptual comparison. Never commit image hashes as PHPUnit assertions (machine-dependent; breaks CI) - committed tests assert structure only (dimensions, event counts, no exceptions).

### 3.5 Interactive JS verification

Four layers, cheapest first. Used by F1-F4 and any phase touching a view with inline JS.

1. **Behavior inventory (the primary net).** Before touching a view, enumerate every interactive behavior into a port-and-tick checklist in your session notes: `grep -oE "x-data|x-model[^ ]*|x-show|x-if|@click[^=]*|\\$\\(" <view>` plus every inline `function`. Most of these behaviors have no test and never economically will - the checklist is what guarantees each one is consciously ported, not lost.
2. **Dusk tests for money flows only** (run in CI, never locally - Section 3.7 of P0): event create with venue + ticket then guest page shows it; event edit changing date/time persists; schedule settings change-save-reload persists; calendar month navigation + event click-through (via 2 of its 6 consumer views); ticket add/remove rows produce correct hidden-form state. Skip Dusk for the import wizard and graphic designer - manual QA covers them.
3. **Mount smoke - `storage/refactor/js_smoke.sh`:** convention: every NEW Vue mount sets `data-vue-mounted="1"` on its root element in `mounted()`. The script runs `chrome --headless=new --dump-dom --virtual-time-budget=8000 --ignore-certificate-errors <url>` for each affected page and greps for (a) the mount markers, (b) leftover un-rendered `{{ ... }}` mustaches. Restart Herd/PHP-FPM first (Landmine L7).
4. **Non-JS-client regression:** each JS phase keeps/adds a Feature test POSTing the exact server-visible field set of every affected form, asserting the resulting DB row - this catches Vue `:value` hidden inputs nulling NOT NULL boolean columns (Landmine L6).

**What automation cannot catch here** (goes on the phase's manual-QA list): drag-drop correctness and feel, file-input preview flows, Flatpickr popup interactions, focus/keyboard behavior, debounce/autosave timing, clipboard, `window.open` flows, Stripe Elements interplay, handler-ordering semantics of ported jQuery delegation, RTL and dark-mode visuals.

### 3.6 Route-surface freeze

Used by P6 and every controller-split phase (P7-P10, O3, O4).

**`storage/refactor/routes_snapshot.sh` design (build in P1):** captures `php artisan route:list --json` under FOUR env permutations - `routes/web.php` branches on all of them and the phpunit env never loads the hosted domain-group blocks:

| File suffix | Env |
|---|---|
| `hosted-nexus` | `IS_HOSTED=true IS_NEXUS=true APP_TESTING=false` |
| `hosted-nonnexus` | `IS_HOSTED=true IS_NEXUS=false APP_TESTING=false` |
| `selfhosted` | `IS_HOSTED=false` |
| `testing` | the phpunit config (`APP_TESTING=true IS_NEXUS=true`) |

Per permutation, write THREE files:

- `surface.<env>.json`: `jq 'sort_by(.uri, .name, .method) | map({domain, method, uri, name, middleware})'` - NO action column. **Must diff empty after any phase.**
- `full.<env>.json`: same projection plus `action`. In split phases its diff may touch ONLY `action` values - review that diff line by line as the old->new mapping.
- `order.<env>.json`: the UNSORTED raw list. **P6 requires this to be byte-identical** (registration order is behavior - Landmine L8).

The script refuses to run if `bootstrap/cache/config.php` exists (cached config would ignore the env permutations). The `middleware` column is resolved middleware including controller-constructor `$this->middleware()` - losing constructor middleware is the number-one silent break when splitting a controller, and this column catches it.

**Extra smoke whenever routes are touched:** `php artisan route:cache && php artisan route:clear` (route:cache fails loudly on name collisions and uncacheable definitions).

### 3.7 Static analysis net

- **Larastan** (installed in P1 with owner approval): `phpstan.neon` at repo root, level 5 - catches the transplant-error class (undefined methods/properties, argument-type mismatches on moved code) without drowning in missing-generics noise. Paths: `app`, `routes`, `database/seeders`. Generate `phpstan-baseline.neon` once in P1 and commit it.
- **Per-phase gate:** `vendor/bin/phpstan analyse --memory-limit=2G` exits 0 (no NEW errors vs baseline).
- **Ratchet rule:** the baseline may only shrink. When a phase rewrites code whose errors were baselined, regenerate at phase END and verify `git diff phpstan-baseline.neon` is deletions-only. Never regenerate to hide a new error - fix it or annotate `@phpstan-ignore` with a justifying comment.
- **Pint:** `vendor/bin/pint --dirty` before each commit; verification form `vendor/bin/pint --test --dirty`. Repo-wide pint is forbidden (Rule R6).
- **Lint companion:** `git diff --name-only HEAD -- '*.php' | xargs -n1 php -l`.

### 3.8 The postflight checklist (every phase ends with this)

Ordered. Stop at the first failure.

| # | Gate | Command | When | Pass |
|---|------|---------|------|------|
| 1 | Style | `vendor/bin/pint --dirty`, then re-review the diff it made | always | no unreviewed hunks |
| 2 | Static | `vendor/bin/phpstan analyse --memory-limit=2G` | always | exit 0 |
| 3 | Tests | `php artisan test` | always | green, full suite |
| 4 | Blade compile | `php artisan view:clear && php artisan view:cache && php artisan view:clear` | views touched | exit 0 (catches Landmine L2 repo-wide) |
| 5 | Route surface | regenerate `current/` route snapshots; diff per Section 3.6; `route:cache` smoke | routes or controllers touched | surface + order diffs empty (action-only in full, split phases) |
| 6 | HTML snapshots | `php storage/refactor/html_snapshot.php --out storage/refactor/current/html` then `diff -ru` | views touched | empty diff |
| 7 | Graphics | regenerate + diff `manifest.sha256` | designs/AbstractEventDesign touched | identical hashes |
| 8 | JS build | `npm run build` | JS/Vite touched | exit 0, no new warnings |
| 9 | Mount smoke | `bash storage/refactor/js_smoke.sh` (restart Herd/FPM first) | JS or affected views touched | all markers present, no raw mustaches |
| 10 | Non-JS form posts | the phase's raw-field-set Feature tests | JS phases | green (subset of gate 3 - verify explicitly) |
| 11 | Tree review | `git status --porcelain` + `git diff --stat` (+ `git diff --stat routes/web.php` on split phases) | always | only in-scope files; baselines untracked |
| 12 | Ledger | `BUGS_FOUND.md` updated for anything found | always | entries and pinned tests cross-reference |
| 13 | Manual QA | present the phase's Appendix C checklist to the owner | always | owner sign-off |
| 14 | CI | push the branch; feature-tests + dusk jobs green | always | phase not done until CI passes |

---

## 4. Known landmines

Repo-specific traps that have burned sessions before. Read before every phase.

- **L1 - `request()` vs `$request` are NOT interchangeable.** Inside `EventRepo::saveEvent()` some lines use the global helper (`request()->schedule_type` at EventRepo.php:831, 846, 860, 885) while the method also receives a `$request` parameter - and `WhatsAppWebhookController` / `EventbriteController` pass a SYNTHESIZED Request into `saveEvent`, so on those paths `$request !== request()`. "Normalizing" one to the other changes which request recurring events read their config from. Preserve tokens (Rule R3).
- **L2 - Adjacent Blade directives silently break compilation.** `@endif@endif` (no whitespace between) drops the second directive and produces a fatal at the next `@endforeach`/`@endif` - at RUNTIME, not at edit time. Always separate directives with whitespace. Gate 4 (`view:cache`) compiles every view and catches this repo-wide.
- **L3 - Vue compiles server-rendered HTML as templates.** The app uses Vue's full build with the runtime template compiler. User-controlled text inside a Vue-mounted element needs `v-pre` or `<x-user-text>` (Blade `{{ }}` escaping does NOT stop Vue mustache evaluation). And `@json()` inside a Vue-BOUND ATTRIBUTE mangles the DOM and kills the mount silently - pass data via a data-island script + props instead. Never remove existing `v-pre`/`x-user-text` guards while moving markup.
- **L4 - Vite modules are strict mode and deferred.** Inline `<script>` blocks run sloppy-mode, in document order, sharing implicit globals. Moving them into Vite entries changes both: implicit globals (`app = createApp(...)` at event/edit.blade.php:4839, top-level `var` read by OTHER script blocks) throw or vanish. Before moving a block, inventory every top-level identifier it shares across script-block boundaries (grep the sibling blocks) and pin shared ones to `window.` explicitly.
- **L5 - Import Vue as `'vue/dist/vue.esm-bundler.js'`.** In-DOM Blade templates need the runtime template compiler. The default `'vue'` bundler entry is runtime-only and renders NOTHING, silently. (The mount-marker convention in Section 3.5 exists to catch exactly this class of silence.)
- **L6 - Vue `:value` hidden inputs null out NOT NULL booleans for non-JS clients.** Empty submitted strings pass through `ConvertEmptyStringsToNull` and hit NOT NULL columns. `EventRepo::saveEvent` deliberately re-coerces 11 boolean flags via `has()`+`boolean()` - preserve that block exactly, and keep the raw-field-set Feature tests (Section 3.5 layer 4).
- **L7 - PHP opcache serves stale bytecode over HTTP.** Never A/B-verify a change through the browser without restarting Herd/PHP-FPM; prefer CLI verification (kernel-dispatch snapshots, tinker). This is why `html_snapshot.php` dispatches in-process.
- **L8 - Route registration ORDER is behavior.** A domain-less route shadows every host (documented in routes/web.php:55-57); `/dashboard` beats `/{subdomain}` only by registering first; the `/{slug?}` catch-all must stay LAST. Any route-file restructuring must keep the unsorted `route:list` byte-identical (Section 3.6).
- **L9 - `marketing_url()`, never `route('marketing.*')`, in AP/shared views.** Marketing routes are nexus-gated (`IS_NEXUS` is independent of `IS_HOSTED`); a `route('marketing.*')` call 500s on hosted-non-nexus installs.
- **L10 - CSP.** No inline `on*` handlers anywhere; inline scripts need `{!! nonce_attr() !!}`. Bundled Vite files need no nonce - extraction phases reduce the inline-script surface, they must not add unnonced inline scripts.
- **L11 - `MetaAdsServiceFake` is runtime code, not test debris.** `BoostController` and several Jobs fall back to it when Meta is not configured. It stays in `app/Services/`.
- **L12 - `UrlUtils::decodeId` has a dual decode path** (Sqids first, then a legacy base64 fallback). Any wrapper (P5's trait) must call `decodeId` itself, never reimplement half of it.
- **L13 - Tests never load the hosted domain-group routes.** The phpunit env is `APP_TESTING=true IS_NEXUS=true`, which takes the flat-route branches. Only the 4-permutation route snapshot (Section 3.6) covers the domain-scoped blocks - a green suite alone does NOT prove a route refactor safe.

---

## 5. Semantic-preservation rules

When a "standard Laravel modernization" is NOT behavior-preserving, and what to do instead.

### (a) Inline `$request->validate()` -> FormRequest

A FormRequest validates during controller-method dependency resolution: after ALL route middleware, but before the FIRST statement of the method body. An inline `validate()` runs wherever it sits. Both throw the same `ValidationException` (same redirect-back-with-errors, same 422 JSON, same default error bag, same `stopOnFirstFailure=false`). Therefore convert ONLY when ALL of these hold:

1. The `validate()` call is the first executable statement of the method. Anything before it - `decodeId`/`findOrFail`, `abort`, an auth check with redirect, a log write, a session write - is a semantic barrier: converting flips response ordering for requests that fail both checks (today 404-before-422 becomes 422-before-404).
2. The rules array is a literal. Rules built from loops/config/DB (e.g. the `$request->validate($rules)` sites around EventController.php:2240) stay inline.
3. It is the only `validate()` on that code path (multi-stage validation stays inline).
4. No `validateWithBag()` (if found, keep inline or replicate with the `$errorBag` property - decide per site with a comment).

Inside the new FormRequest: `authorize()` returns `true` unconditionally (authorization stays wherever it lives today); no `prepareForValidation`; messages untouched. In the controller body keep `$request->all()`/`input()` reads AS-IS - do NOT swap to `$request->validated()`; code may rely on unvalidated fields (`saveEvent` does `fill($request->all())`), and that swap changes persisted attributes. Expect roughly half of the 124 inline sites to convert; keep an inventory of kept-inline sites and reasons in the Campaign Log.

### (b) Inline auth checks -> Policies

The same `isMember($subdomain)` check returns THREE different failure shapes across RoleController (redirect home with flash error; redirect back with flash error; JSON `{success: false}` 403). `$this->authorize()` produces none of those. Convert to `authorize()` ONLY where today's failure is already a bare `abort(403)`/`abort_unless(..., 403)` with no custom payload. Never convert a custom-redirect or custom-JSON site - at most, replace the boolean EXPRESSION with a policy call inside the same `if`, keeping the response line byte-identical, and only when the policy method is provably the same expression. Sites that check by `$subdomain` string before any model is loaded stay inline. P18 = fill the two stub policies for the ~28 existing `authorize()` call sites only; a mass conversion is out of scope.

### (c) `is_deleted` -> LOCAL scope, never a global scope

Zero queries in the codebase filter FOR deleted rows, but hundreds of UNFILTERED reads (direct `findOrFail` by id, relationship loads, `BackupService` restore, admin surfaces, `exists()` checks) implicitly include deleted rows today. A global scope would newly exclude rows at every one of those sites: silent 404s, changed counts, broken restores. The local scope `scopeNotDeleted()` is a drop-in for exactly the 84 explicit filter sites and changes nothing else. Replace only unqualified `->where('is_deleted', false)` on a builder whose model is known to carry the trait; qualified-column variants (`'roles.is_deleted'`) and raw `DB::` queries keep the literal.

### (d) Role-type magic strings -> string-backed enum, `->value` only

`app/Enums/RoleType.php`: `enum RoleType: string { case Venue = 'venue'; case Talent = 'talent'; case Curator = 'curator'; }`. Mechanical means: replace the literal token with `RoleType::Venue->value` (identical string at runtime; `==`/`===` untouched). Where the expression is exactly `$role->type == 'venue'` you MAY use the pre-existing `$role->isVenue()` (verified identical, Role.php:634) - but create no new helpers.

NEVER touch: DB values, migrations, seeders; API request/response payloads; validation rule strings (`'in:venue,talent,curator'` stays a literal); Blade comparisons (view phases own those files); JS/`@json` data. Above all: NO `casts()` entry mapping `type` to the enum - an enum cast changes the runtime type of `$role->type` everywhere at once (Blade string comparisons, JSON serialization) and is exactly the non-mechanical step this rule forbids.

### (e) `decodeId` preamble -> model finder trait, NOT route binding, NOT middleware

`app/Models/Concerns/HasEncodedId.php` with `findByHashOrFail($hash)` and `findByHash($hash)` delegating to `UrlUtils::decodeId` (L12). Parity is structural: `decodeId(garbage)` returns null and `findOrFail(null)` already 404s.

Route model binding is REJECTED for this campaign: `{hash}` parameter names are ambiguous across models (Newsletter vs Event vs template routes); ~40% of decode sites read request-BODY params (`venue_id`, `members` keys, `curators[]`) that bindings cannot see; sites vary between `findOrFail`, nullable `find`, and chained builders; and binding resolves in `SubstituteBindings` BEFORE method-body side effects that currently run before the decode. Renaming route parameters would also change the route surface (forbidden by R1).

Replace ONLY these exact shapes: `Model::findOrFail(UrlUtils::decodeId($x))`; the two-line variant where the decoded id has NO later use; `Model::find(UrlUtils::decodeId($x))`. Leave chained builders (`Event::with(...)->findOrFail(decodeId(...))`, the `where('is_deleted', ...)->findOrFail(...)` site at EventRepo.php:341) and any site that reuses the decoded id.

### (f) Model `boot()` closures -> Observers: yes, do it (P16)

Mechanical because observers and boot closures ride the same model-event dispatcher: `saveQuietly()`/`withoutEvents()` bypass both identically (the `BackupService` restore paths, which rely on `saveQuietly`, behave the same before and after); listeners fire in registration order and NO observers exist today, so there is no interleaving hazard. Rules: one model per commit; ONE observer class per model registered via `#[ObservedBy(...)]` on the model; each `static::saving(closure)` body moves VERBATIM into one observer method (statement order intact - never split one closure across methods); Role's `saving`/`deleting`/`updating` hooks stay three separate observer methods (different trigger sets); never leave a hybrid where some closures remain in `boot()` and some live in the observer for the same event. While moving, confirm no closure returns `false` (a `false` return cancels the operation in both mechanisms - must be preserved if found).

---

## 6. Phase work orders

### 6.1 Phase table

Size: S/M/L for one focused session (L may take two - see Section 1). Risk names the specific thing that can go wrong.

| ID | Name | Size | Risk | Depends on |
|----|------|------|------|-----------|
| P0 | Test database isolation | S | Low | - |
| P1 | Verification harness + Larastan | S | Low | P0 |
| P2 | MarketingController data extraction | S | Low | P1 |
| P3 | Event-graphic designs dedup | M | Med | P1 |
| P4 | notDeleted() scope + RoleType enum | M | Low | P1 |
| P5 | Encoded-ID finder trait | M | Low | P1 |
| P6 | routes/web.php physical split | M | Med | P1 |
| P7 | RoleController split A (guest/public) | L | Med | P6; P4+P5 preferred first |
| P8 | RoleController split B (admin/team/AI) | L | Med | P7 |
| P9 | EventController split | L | Med | P6 |
| P10 | TicketController split | M | Med | P6 |
| P11 | saveEvent characterization matrix (TESTS ONLY) | M | Low | P0 |
| P12 | saveEvent extract-method decomposition | L | High | P11 |
| P13 | saveEvent collaborator extraction | M | Med | P12 (optional) |
| P14 | RoleController update()/viewGuest() decomposition | L | High | P8 |
| P15 | TicketController checkout()/rsvp() decomposition | M | High | P10 |
| P16 | Model boot() -> Observers | M | Low-Med | P1 |
| P17 | FormRequest conversion wave | M | Med | P7-P10 |
| P18 | Policy consolidation | S | Low | P17 (optional) |
| F1 | event/edit JS extraction to Vite | L | Med | P1 |
| F2a | role/edit JS extraction to Vite | L | Med | P1 |
| F2b | role/edit jQuery/Alpine -> Vue per widget | L | High | F2a |
| F3 | Calendar partial JS extraction (6 consumers) | L | High | F1 or F2a |
| F4 | event/import + graphic/show JS extraction | M | Med | F1 |
| F5 | event/show-guest server-side detangle | L | Med | P14 preferred (optional) |
| O1 | for-* marketing consolidation (34 pages) | L | Med | P1 (stretch) |
| O2 | NoFakeEmail blocklist -> data file | S | Low | P1 (optional) |
| O3 | AdminController + NewsletterController splits | M | Med | P6 (optional) |
| O4 | Marketing route-block dedup | M | Med | P6 (stretch) |

### 6.2 Dependency graph

```
P0 - P1 -+- P2                       (momentum: pure data move)
         +- P3                       (dedup with golden-image proof)
         +- P4 - P5                  (token conventions BEFORE splits)
         +- P6 -+- P7 - P8 - P14     (Role axis)
         |      +- P9
         |      +- P10 - P15
         |      +- O3, O4
         +- P16
         +- F1 -+- F4                (frontend track, parallel to backend)
         |      +- F3 (also after F2a)
         +- F2a - F2b
         +- F5, O1, O2
P0 - P11 - P12 - P13                 (EventRepo axis, independent of splits)
P7..P10 - P17 - P18
```

### 6.3 Ordering rationale

- **P2/P3 first among real refactors:** large-win, low-ambiguity dedup that proves the whole workflow (tests-first, snapshot verification, revert discipline) before anything with real semantic risk.
- **Conventions (P4/P5) before splits (P7-P10):** they touch the same lines; doing tokens first means every later move-diff is a PURE move, verifiable with `git diff --color-moved=dimmed-zebra`. Done after, the convention pass would straddle 10+ new files and pollute extraction diffs.
- **Splits before god-method decomposition (P14/P15):** splits are verified by a mechanical oracle (action-only route diff) and need no deep understanding - do them while the code is still ugly. Decomposition sessions then work in 1-2k-line files instead of a 6k-line file, and the split's characterization tests are exactly the ones decomposition reuses.
- **The saveEvent axis (P11 -> P12) is independent of splits** (EventRepo is not a controller) and can interleave anywhere after P0.
- **The F-track shares no files with backend phases** - interleave freely to vary session types. Only F2a -> F2b (extract before convert) and F1-before-F3 (prove the pattern on a single-consumer page before the six-consumer partial) are ordered.
- **Optional/stretch phases** (P13, P18, F5, O1-O4) can be skipped without harming the rest. O1 and O4 have byte-identical-output gates: attempt them only if the gate holds, abandon rather than approximate.

### 6.4 Do-NOT list

Standing decisions - do not revisit them mid-phase:

- `MetaAdsServiceFake` stays in `app/Services/` (runtime fallback - L11).
- `app/helpers.php`: all 30 functions are used; several (`_base_domain()`, `marketing_url()`) are load-bearing in routes and CLAUDE.md. Optional tidying only, never removal.
- `RowDesign`'s GD helpers with different signatures stay in `RowDesign` (P3).
- `ListDesign::addText` override stays (it intentionally overrides the base).
- Divergent design-class method bodies are NOT unified - they differ on purpose (per-layout aspect handling).
- `ConvertsLocationToVenue` (app/Services/Concerns/) is NOT merged with saveEvent's venue dedup - different scoping (user-scoped vs global), ranking, and creation defaults.
- No SQLite anywhere; MySQL only. No `.env.testing`. No `testing` DB connection.
- The WP pricing feature lists and WP header/footer links are manually curated - never touched by any phase.

---

### P0 - Test database isolation

**Goal:** `php artisan test` stops wiping the dev database, so every later phase can run the suite freely. Today `phpunit.xml` line 26 hardcodes `DB_DATABASE=eventschedule` (the live dev DB) and every Feature test runs `RefreshDatabase` (`migrate:fresh`).

**Targets:** `phpunit.xml`, `tests/TestCase.php`, `.github/workflows/test.yml`, `CLAUDE.md`.

**Steps:**

1. Ask the owner to create the database (R8):
   ```sql
   -- check the app user's host first: SELECT user, host FROM mysql.user WHERE user='eventschedule';
   CREATE DATABASE IF NOT EXISTS eventschedule_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   GRANT ALL PRIVILEGES ON `eventschedule_test`.* TO 'eventschedule'@'localhost';  -- or @'%' per the check
   FLUSH PRIVILEGES;
   ```
   Charset/collation match the `config/database.php` mysql defaults.
2. `phpunit.xml` line 26: `<env name="DB_DATABASE" value="eventschedule_test" force="true"/>`. The `force="true"` matters: PHPUnit `<env>` does not override an already-exported shell variable without it, so a stray `export DB_DATABASE=eventschedule` would silently re-point tests at dev.
3. Add a refuse-to-run guard to `tests/TestCase.php::setUp()`, BEFORE `parent::setUp()` (Laravel's setUp boots the app and fires `RefreshDatabase` -> a check after it is too late; `getenv()` works pre-boot because PHPUnit `putenv()`s the phpunit.xml values):
   ```php
   protected function setUp(): void
   {
       if (! str_ends_with((string) getenv('DB_DATABASE'), '_test')) {
           self::fail('Refusing to run: DB_DATABASE must be a dedicated *_test database (see phpunit.xml). Got: ' . getenv('DB_DATABASE'));
       }

       parent::setUp();

       $this->withoutVite();
   }
   ```
4. `.github/workflows/test.yml`, feature-tests job: change `MYSQL_DATABASE: eventschedule` to `eventschedule_test` and the matching `sed`/env line, since phpunit.xml now forces the new name. The dusk job (`laravel_test` DB) is untouched.
5. `php artisan config:clear && php artisan route:clear && php artisan view:clear`. Verify `bootstrap/cache/config.php` does not exist afterward - a cached config bypasses phpunit env values and is the classic way this isolation silently fails.
6. Verify isolation, then stability:
   ```bash
   mysql -u eventschedule -p -e "SELECT COUNT(*) FROM eventschedule.users;"   # before
   php artisan test                                                            # first run migrates eventschedule_test
   php artisan test                                                            # second run confirms stability
   mysql -u eventschedule -p -e "SELECT COUNT(*) FROM eventschedule.users;"   # after - MUST be identical
   ```
   Record any pre-existing test failures in the Campaign Log as "known reds" - later phases compare against this inventory.
7. Same commit: update CLAUDE.md. Replace the rule `- **Never run tests without asking first** - Tests will empty the database` with:
   ```markdown
   - **`php artisan test` is safe to run locally** - PHPUnit uses the dedicated `eventschedule_test` MySQL database (forced in `phpunit.xml`); it never touches the `eventschedule` dev database. `tests/TestCase.php` refuses to run against any non-`*_test` database.
   - **Never run `php artisan dusk` locally without asking first** - Dusk swaps `.env` with `.env.dusk.local` and wipes the database it points at. Browser tests run in CI.
   ```
   And rewrite the `## Testing` section's warning paragraph to match.

**Do NOT:** create `.env.testing` (a second, forgettable config source - phpunit.xml stays the single source of truth); add a `testing` connection to `config/database.php`; install paratest; touch `phpunit.dusk.xml` or `.env.dusk.local`.

**Dusk policy for the whole campaign:** never run `php artisan dusk` locally - it swaps `.env` with `.env.dusk.local`, which points at the dev DB, and `DatabaseTruncation` wipes it. Dusk verification happens in CI on each phase PR. If a local Dusk run is ever genuinely needed: ask the owner, create `eventschedule_dusk`, and repoint `.env.dusk.local` first.

**Verification:** step 6 counts identical; suite green twice; CI green. **Risk:** low - the named risk is Dusk config accidentally inheriting the change.

---

### P1 - Verification harness + Larastan

**Goal:** build every tool the postflight checklist references, capture first baselines, install the static-analysis net.

**Targets (all new):** `storage/refactor/` per Section 3.1; `phpstan.neon` + `phpstan-baseline.neon`; `BUGS_FOUND.md` stub; `tests/Support/NormalizesHtml.php`.

**Steps:**

1. Create `storage/refactor/` with its `.gitignore` (`baselines/`, `current/`).
2. Write `routes_snapshot.sh` (Section 3.6 spec: 4 permutations x 3 projections, refuses to run when config is cached) and a small diff helper or documented `diff`/`jq` one-liners.
3. Write `html_snapshot.php` (Section 3.3 spec: kernel dispatch, `putenv` array drivers pre-bootstrap, frozen clock, 2 normalizations, `urls.php` manifest with the 34 `for-*` URIs).
4. Write `graphic_baseline.php` (Section 3.4 spec: in-memory models, layout/count/locale/flag matrix, `manifest.sha256`, `--diff` triage mode). Verify the `EventGraphicGenerator` constructor signature against `GraphicController` before writing.
5. Write `js_smoke.sh` (Section 3.5 spec: headless Chrome dump + marker/mustache greps; page list supplied per phase).
6. Run all snapshot scripts once against the clean tree; diff each against itself regenerated (must be empty twice in a row - proves determinism on this machine). If a page or image is NOT stable across two runs on identical code, find the variance source and pin it (fixture, frozen clock, or a documented normalization) before declaring P1 done.
7. Ask the owner (R8), then `composer require --dev larastan/larastan`. Create `phpstan.neon`:
   ```neon
   includes:
       - phpstan-baseline.neon
   parameters:
       level: 5
       paths:
           - app
           - routes
           - database/seeders
   ```
   Then `vendor/bin/phpstan analyse --memory-limit=2G --generate-baseline` and commit both files. No `excludePaths` without a justifying comment.
8. Create `BUGS_FOUND.md` with the Appendix B template at top and no entries.
9. Create `tests/Support/NormalizesHtml.php` (same two normalizations as the snapshot script) for use inside Feature tests.

**Verification:** every script runs green on the untouched tree; self-diff empty; `phpstan analyse` exits 0; `php artisan test` green. **Risk:** low - the named risk is snapshot scripts that pass by accident (empty output diffs empty); assert each script produced a nonzero file count.

---

### P2 - MarketingController data extraction

**Goal:** move ~3,100 lines of hardcoded data arrays out of `app/Http/Controllers/MarketingController.php` (5,405 lines): `getComparisonData()` (~2,218 lines at ~line 1455) and `getReplacementData()` (~866 lines at ~line 3673). Pure data relocation; proves the campaign workflow end to end.

**Tests first:**

1. Golden-fixture tests: call each method (via reflection if not public), `json_encode` the FULL return value with `JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE`, and compare to committed fixtures `tests/fixtures/comparison_data.json` and `tests/fixtures/replacement_data.json`. Generate the fixtures from the UNMODIFIED code (golden-file pattern: write the fixture when absent, assert against it when present), inspect them, commit.
2. Feature smoke: GET every `marketing.compare_*` and `marketing.replace_*` route (flat-registered under the phpunit env), assert 200 + one distinctive per-competitor string.
3. HTML snapshot baseline for two representative pages (e.g. `/eventbrite-alternative`, one replacement page) via `html_snapshot.php`.

**Steps:**

1. Create `app/Services/Marketing/ComparisonData.php` and `ReplacementData.php`: plain classes, `public static function all(): array` (verbatim array cut-paste), plus whatever keyed accessor the controller call sites need.
2. The two controller methods become one-line delegates (they stay: CLAUDE.md and call sites reference them by name - R7).
3. Re-run fixture tests (byte-identical JSON), route smokes, and the HTML snapshot diff (empty).

**Verification:** gates 1-3, 5 (controller touched), 6, 11-14. **Risk:** low - arrays contain no code. **Manual QA:** open two comparison and one replacement page, view-source spot check.

---

### P3 - Event-graphic designs dedup

**Goal:** remove ~1,000 lines of copy-pasted GD helpers from `app/Services/designs/GridDesign.php` (906), `ListDesign.php` (835), `RowDesign.php` (578), which extend `app/Services/AbstractEventDesign.php` (2,126) yet duplicate helpers the base lacks (`addEventFlyerImage`, `loadAndDisplayEventImage`, `resizeAndDisplayImage`, `applyRoundedCorners`, `applyMaskToImage`, `createPlaceholderBackground`, `addEventQRCode`, ...).

**Verified facts that shape the work:** `applyRoundedCorners` and `applyMaskToImage` are byte-identical between Grid and List; Row carries VARIANTS with DIFFERENT SIGNATURES for several helpers (e.g. Row's `addEventFlyerImage(Event $e, $x, $y, $width, $height)` vs Grid/List's `(Event $e, $x, $y)`). An incompatible child override of a concrete base method is a PHP fatal - those helpers must NOT go to the abstract base.

**Tests first:**

1. Golden images: run `graphic_baseline.php` to capture the baseline manifest (session-local, not committed).
2. Committed structural unit tests: for fixed inputs, each of the 3 layouts generates without exception and returns expected dimensions/event counts. Plus a smoke test that simply instantiates all three design classes (catches signature-collision fatals that nothing else compiles).

**Steps:**

1. Build the evidence table first: for each duplicated helper, machine-diff the three bodies (`diff <(sed -n 'a,bp' GridDesign.php) <(sed -n 'c,dp' ListDesign.php)` etc.). Classify: identical-in-3 / identical-in-2 (Grid+List) / signature-conflicts-with-Row / divergent. Paste the table into the Campaign Log.
2. Identical-in-3 with identical signatures -> hoist to `AbstractEventDesign` (protected), delete from all three children. One helper (or one tight cluster) per commit.
3. Identical-in-2 (Grid+List), or anything whose Row variant has a conflicting signature -> new trait `app/Services/Concerns/RendersGdImages.php`, used by Grid and List only. Row untouched.
4. Divergent bodies stay where they are (Do-NOT list). `ListDesign::addText` override stays.
5. After each commit: regenerate `current/` golden manifest and diff - must be identical.

**Verification:** gates 1-3, 7, 11-14. ANY manifest hash change = the hoist changed behavior; fix it, never relax the comparison. **Risk:** medium - signature-collision fatals on code paths tests do not compile (mitigated by the instantiation smoke + golden matrix). **Manual QA:** open the event-graphic designer in the AP, generate and download each of the three layouts once.

---

### P4 - notDeleted() scope + RoleType enum

**Goal:** kill the two widest string repetitions: `->where('is_deleted', false)` (84 sites) and the `'venue'`/`'talent'`/`'curator'` literals (131/87/22 sites), per rules 5(c) and 5(d).

**Tests first:** Unit tests only (this phase is token-mechanical; the full suite is the characterization): `Role::notDeleted()->toSql()` equals `Role::where('is_deleted', false)->toSql()`; enum case values equal the exact current strings.

**Steps:**

1. New `app/Enums/RoleType.php` (rule 5(d)) and `app/Models/Concerns/HasNotDeletedScope.php` with `scopeNotDeleted($query)` returning `$query->where('is_deleted', false)`.
2. Grep for which models the filter is applied against; attach the trait to exactly those (survey says Role, Event, Sale, User, NewsletterSegment - verify).
3. Mechanical replacement, ONE pattern per commit, with grep-count bookkeeping recorded in the commit message (replacement count before == after):
   - `->where('is_deleted', false)` -> `->notDeleted()` only where the receiving builder's model is known and carries the trait (direct `Model::...`, relation builders, `whereHas` closures on trait-carrying models). Skip qualified columns (`'roles.is_deleted'`) and raw `DB::` sites.
   - `'venue'`/`'talent'`/`'curator'` -> `RoleType::X->value` in PHP comparisons/assignments/`where('type', ...)`, respecting the NEVER-touch list in 5(d). Blade files are NOT touched in this phase.
4. Re-grep: remaining literal counts must equal the documented skip inventory.

**Verification:** gates 1-3, 11-14; plus a route-surface self-check (cheap sanity - nothing should change). **Risk:** low - the named risk is replacing a literal inside a validation rule string or data array (forbidden by 5(d); the per-pattern commits keep review tight).

---

### P5 - Encoded-ID finder trait

**Goal:** collapse the `UrlUtils::decodeId` decode-or-404 preamble (231 controller call sites) per rule 5(e).

**Tests first:** 404-parity triples on two representative routes (e.g. `event.edit`, `newsletter.edit`): valid hash -> 200; garbage hash -> 404; hash of a nonexistent id -> 404. Plus one nullable-`find` site test (e.g. `venue_id` in an event-store payload) proving an invalid hash follows the existing null path, not a 404.

**Steps:**

1. New `app/Models/Concerns/HasEncodedId.php`:
   ```php
   public static function findByHashOrFail($hash) { return static::findOrFail(UrlUtils::decodeId($hash)); }
   public static function findByHash($hash) { return static::find(UrlUtils::decodeId($hash)); }
   ```
   (Delegating to `decodeId` preserves the dual Sqids/base64 path - L12.)
2. Attach to the models grep shows being resolved this way (Event, Role, Sale, Newsletter, NewsletterSegment, Webhook, Carpool models, EventPart, ...).
3. Replace ONLY the exact shapes listed in rule 5(e), one controller per commit. Leave chained builders and reused decoded ids.
4. Re-grep `UrlUtils::decodeId` in controllers; remaining count must equal the documented leave-list.

**Verification:** gates 1-3, 11-14. **Risk:** low - the named risk is replacing a site where the decoded id is reused later in the method (forbidden by 5(e)).

---

### P6 - routes/web.php physical split

**Goal:** `routes/web.php` (1,383 lines) becomes an order-preserving manifest of `require` statements over new files in `routes/web/`. Registration order is behavior (L8) - this phase's entire risk is ordering.

**Tests first:** none new - the P1 route snapshots plus `tests/Feature/RouteLoadTest.php` are the oracle. Capture fresh baselines (all 4 permutations, all 3 projections) at phase start.

**Steps:**

1. Cut the file into requires IN THE EXACT CURRENT SEQUENCE. Survey-derived map (re-derive the ranges by content before cutting):
   ```php
   // routes/web.php (manifest - ORDER IS LOAD-BEARING, see L8)
   Route::get('/robots.txt', ...);               // stays inline, first
   require __DIR__.'/web/hosted-guest.php';      // the hosted-subdomain group + its else branch - the if/else moves TOGETHER
   require __DIR__.'/web/app-update.php';        // (or leave these few lines inline)
   require __DIR__.'/auth.php';                  // position unchanged
   require __DIR__.'/web/public.php';            // sitemap, unsubscribe, tracking, webhooks, public ticket/feedback
   require __DIR__.'/web/app.php';               // the entire auth+verified+app_subdomain group incl. admin (~400 lines - one middleware group, do NOT re-group its interior)
   Route::get('/tmp/event-image/{filename?}', ...);   // stays inline, position between groups matters
   Route::get('/map-image/{id}', ...)->name('map.image');
   require __DIR__.'/web/marketing.php';         // the whole is_nexus if/else (all variants + non-nexus redirects)
   require __DIR__.'/web/blog.php';
   require __DIR__.'/web/selfhosted-guest.php';  // path-based {subdomain} counterparts
   Route::get('/{slug?}', [HomeController::class, 'landing'])->name('landing');  // LAST - catch-all
   ```
2. Each new file gets its own `<?php` + `use` imports (imports are per-file; copy the block, trim only what the file provably does not use - route:list catches mistakes).
3. NEVER wrap a `require` in a condition (it would split an if/else pair) and NEVER convert to `Route::group(fn () => require ...)` (adds group nesting attributes). Conditionals stay INSIDE their files exactly as-is; requires are unconditional.
4. Optionally split `marketing.php` further into per-branch files required from inside its if/else arms - same order guarantee.

**Verification:** gates 1-3, 5, 11-14 - and specifically: the UNSORTED `order.<env>.json` must be byte-identical for ALL FOUR permutations, plus `route:cache`/`route:clear` smoke. Also boot the branch tests never load: `IS_HOSTED=true APP_TESTING=false php artisan route:list | head` (L13). **Risk:** medium - ordering; entirely covered by the unsorted snapshots.

---

### The controller-split recipe (used by P7-P10, O3)

1. Fresh route baselines (Section 3.6).
2. Create `app/Http/Controllers/<New>Controller.php` extending the same base `Controller`, replicating EXACTLY any constructor injection and `$this->middleware()` calls relevant to the moved methods (the middleware column of the route snapshot verifies this).
3. For each method to move, grep its body for `$this->` FIRST: every private/protected helper it calls either moves with it (if used only by moved methods) or goes to a shared trait under `app/Http/Controllers/Concerns/` used by both controllers. NEVER duplicate a helper.
4. Cut methods VERBATIM (R3). Move the `use` imports they need.
5. In the routes file: change ONLY the class in the action array (`[RoleController::class, 'viewGuest']` -> `[RoleGuestController::class, 'viewGuest']`) plus the import. URIs, `->name()`, `->middleware()`, `->where()`, parameter names, and ORDER untouched.
6. Delete moved methods from the old controller outright - no `@deprecated` shims (routes are the only dispatch path into actions). Before deleting, grep the repo for `action(`, `redirect()->action`, and the old `Controller::class` outside routes and tests.
7. Delete the old controller file only when it is empty.

**Split verification (the oracle):** `surface.<env>.json` diffs empty for all permutations; `full.<env>.json` diffs touch ONLY `action` values, and the changed set matches the planned method list exactly - review it line by line. Then the full suite + `RouteLoadTest`.

---

### P7 - RoleController split A (guest/public surface)

**Goal:** move the unauthenticated/guest-facing methods out of `app/Http/Controllers/RoleController.php` (6,046 lines, 84 methods).

**Planned move set (verify the inventory by grepping the routes files before starting):**
- -> `RoleGuestController`: `viewGuest`, `listPastEvents`, `calendarEvents`, `request`, `follow`, `unfollow`, `checkEventPassword`, `guestSearchYouTube`, `showUnsubscribe`, `unsubscribe`, `unsubscribeUser`.
- -> `RoleFollowerController`: `following`, `bulkUnfollow`, `qrCode`, `previewLink`.

**Tests first:** per moved method lacking coverage, a route-level characterization (status, redirect target, one content assertion) in the path-based style the suite uses. `viewGuest` minimum set: schedule home 200; event-by-slug 200; event-by-id 200; event+`?date=` 200; unknown slug 404; embed variant 200.

**Steps:** the split recipe above. **Verification:** split oracle + gates. **Risk:** medium - missed `$this->helper()` coupling (step 3 of the recipe is mandatory, method by method). **Manual QA:** open one schedule guest page, one event page, follow/unfollow once.

---

### P8 - RoleController split B (admin/team/AI)

Same recipe, prereq P7. Planned move set: -> `RoleTeamController` (`createMember`, `storeMember`, `removeMember`, `updateMemberLevel`, `resendInvite`); -> `RoleMergeController` (`mergePreview`, `mergeInto`, the `mergeVenues*` cluster, `dismissVenueMergeSuggestion`); -> `RoleAiController` (the `generateStyle*`/`generateScheduleDetails*`/`getStylePrompt*`/`pollStyleImage*` clusters, `searchYouTube`, `saveVideo(s)`, `getTalentRolesWithoutVideos`, `testImport`). Core CRUD + verification + phone + audit-log + timezone methods stay in `RoleController`. Tests first: route characterization for each moved method lacking coverage (team-member add/remove and one merge flow at minimum). **Manual QA:** add/remove a team member, run one AI-style generation if configured, one merge preview.

---

### P9 - EventController split

Same recipe. `app/Http/Controllers/EventController.php` (4,252 lines, 68 methods):
- -> `EventGuestSubmissionController`: guest import/submit/parse (`showGuestImport`, `showGuestSubmit`, `guestImport`, `checkEmail`, `guestParse`, `guestUploadImage`), booking requests (`showBookingRequest`, `bookingRequest`), fan content (`submitVideo`, `submitComment`, `submitPhoto`), `votePoll`, `suggestPollOption`, `curate`.
- -> `EventModerationController`: the approve/reject pairs (video/comment/photo/poll-option), poll CRUD (`storePoll`, `updatePoll`, `deletePoll`, `togglePoll`), `downloadPhotos`, `clearVideos`.
- -> `EventImportController`: `showImportHub`, `showImport`, `import`, `parse`, `parseEventParts`, `scanAgenda`, `saveEventParts`.
- Stays: CRUD, accept/decline, publish/cancel/restore, flyer + AI-details endpoints.

Note: `EventController` uses the `ConvertsLocationToVenue` trait - the trait import follows whichever controller ends up owning the methods that call it. **Tests first:** guest-import happy path, curate flow, one moderation approve/reject pair, poll vote (some exist in `EventTest` - extend, don't duplicate). **Manual QA:** submit a guest event, approve one fan photo.

---

### P10 - TicketController split

Same recipe. `app/Http/Controllers/TicketController.php` (3,340 lines, 38 methods):
- -> `CheckoutController`: `checkout` (~line 840), `rsvp` (~1448), `success`, `cancel`, `paymentUrlSuccess`/`paymentUrlCancel`, `cancelRsvp`, `release`.
- -> `SaleAdminController`: `sales`, `handleAction`, `resendEmail`, `resendFeedbackEmail`, `sendFeedbackNow`, `cancelFeedback`, `importAttendees*`, `exportSales`.
- -> `PassController`: `passBook`, `passCancelBooking`, `resendPassLink`, `qrCode`, `view`, `scanned`, `scan`, `tickets`.

Existing `TicketingTest` + `PassBookingTest` are the characterization backbone - add only missing route smokes. Move code verbatim; payment flows get zero "improvements" in this phase. **Manual QA:** one free checkout, one QR scan, plus the paid-Stripe manual item from Appendix C.

---

### P11 - saveEvent characterization matrix (TESTS ONLY)

**Goal:** the full safety net for P12/P13, before touching `app/Repos/EventRepo.php::saveEvent()` (one ~1,538-line method, lines ~324-1860). A tests-only phase is legitimate: this matrix is a session of work by itself.

**Build:** `tests/Feature/Characterization/EventSaveCharacterizationTest.php` (split into several classes if it grows past ~600 lines), driven through the real `event.store`/`event.update` routes; direct `EventRepo::saveEvent` calls only for non-HTTP callers' shapes (WhatsApp/Eventbrite synthesized requests). Extend `CreatesScheduleData` with the helpers Section 3.2 names. Keep fixture language `en` so the Gemini translation path never fires (no external calls).

**The matrix - one test per cell, asserting COMPLETE resulting rows across `events`, `roles`, `event_role`, `tickets`:**

- Create x venue paths: (1) no venue; (2) `venue_id` of an existing venue; (3) venue fields creating a NEW venue - assert subdomain generated, gradient set, follower attach when `$followNewRoles`; (4) venue fields matching an existing venue via normalized lookup - assert NO new `roles` row; (5) `venue_id` + unclaimed venue + `venue_details_editable=1` and a blank field clears it; (6) same but flag absent - blank does NOT clear (`has()` vs `filled()`); (7) `claim_venue_ownership` sets owner + `default_role_id`.
- Members: new member creates a talent schedule (a `Role` row); owner auto-match by email; existing unclaimed member hash updates name/email; a CLAIMED member is NOT updated.
- Update-switching-venue: event moves from venue A to B; assert pivot rows.
- Recurrence: weekly + `days_of_week` persisted; recurrence end date; include/exclude dates; switching recurring -> one-time clears the fields.
- Timezone: schedule timezone `America/New_York`, wall-clock input -> stored UTC `starts_at`; the `timezoneOverride` parameter path.
- Tickets/addons/promos/parts: enable with 2 ticket types; update prices/quantities on existing; add + edit + delete in ONE update payload; event parts add/remove.
- Curators: `curators[]` attach with pivot values (`is_accepted`, `group_id`); a previously-attached schedule absent from `curators[]` is detached; venue/member attachments preserved when the curator tab flags are absent from the payload.
- Draft -> publish transition; update of a non-draft with a material change (venue swap) bumps `ical_sequence` (via `saveQuietly`) and dispatches attendee notification ONLY when `notify_attendees` is set (`Notification::fake`).
- Flyer: upload (`Storage::fake`); `clone_flyer_image` copies to a fresh path.
- Audit-log row on create and on update.
- `followNewRoles=false` (Eventbrite/WhatsApp path) attaches no follower.

**Verification:** the new suite green on UNMODIFIED code, committed. Any latent bug found -> R4. **Risk:** low.

---

### P12 - saveEvent extract-method decomposition

**Goal:** decompose `saveEvent` into private methods on `EventRepo` - extract-method ONLY, no collaborator objects (that is P13), no logic edits.

**The seam map** (survey-verified; re-locate by content - the P11 matrix runs after every commit):

| # | Extract as (private) | Survey lines | Responsibility / notes |
|---|---|---|---|
| 1 | `resolveVenue(...): ?Role` | 340-518 | venue_id decode; normalized dedup lookup; venue creation (subdomain, gradient); ownership claim + owner auto-match; follower attach; unclaimed-venue field edits (`has()`/`filled()` dance). Contains the chained `where('is_deleted', ...)->findOrFail` at ~341 - leave it (5(e)) |
| 2 | `resolveMemberRoles(...): array` | 520-623 | members[] -> new talent roles / unclaimed updates; owner auto-match; follower attach |
| 3 | `normalizeCustomFields(...)` | 634-716 | decodes JSON, name translation, `$request->merge(...)` - later code READS the merged values; keep the merge, do not return arrays instead |
| 4 | `filterCustomFieldValues(...)` | 718-748 | drops empties, option whitelisting |
| 5 | `applyEventAttributes(...)` | ~750-830 | `fill($request->all())`, NOT NULL boolean re-coercion (L6), category default, enterprise gate, local->UTC, slug |
| 6 | `applyRecurrenceConfig(...)` | ~831-905 | uses the GLOBAL `request()` helper (L1) - preserve tokens |
| 7 | `applyScheduleDefaultFields(...)` | ~906-931 | venue-membership gate, nullable flags |
| 8 | `applySponsorLogos(...)` | ~932-1010 | Pro sponsor logos |
| 9 | `snapshotChangeState(...): array` | 1011-1035 | draft-transition + old-values snapshot. MUST run BEFORE `save()` (dirty tracking); the inline comments there are the spec |
| - | `$event->save()` STAYS INLINE | ~1036 | the anchor - never extract, never reorder across it |
| 10 | `syncEventRoles(...)` | 1038-1186 | post-save venue handling, curator authoritative-tab detach, `roles()->sync`, per-role loop |
| 11 | `storeFlyerImage(...)` | 1187-1259 | upload > ai_flyer_image > clone_flyer_image priority order |
| 12 | hosted-publish block | 1260-1306 | read it in-session, then name it accurately |
| 13 | `syncTickets(...)` | 1307-1538 | |
| 14 | `syncAddons(...)` | 1539-1669 | |
| 15 | `syncPromoCodes(...)` | 1670-1719 | |
| 16 | `syncEventParts(...)` | 1720-1755 | includes the `whereNotIn(...)->delete()` + reload |
| 17 | `dispatchPostSaveEffects(...)` | 1757-1852 | external sync + webhooks (non-draft), audit chokepoint, `ical_sequence` saveQuietly bump, attendee notify |

**Procedure:** extract top-down, 1-3 seams per commit, running the P11 matrix after EVERY commit. Pass dependencies explicitly as parameters/returns; where the original wrote to a local reused later, return it. `saveEvent` ends as a ~60-line narrative around the inline `save()`.

**Traps:** L1 tokens preserved verbatim; never reorder anything across the `save()` anchor (seam 9 before, seam 10 after - their comments document why); seam 3's `$request->merge` is load-bearing; do not "clean" anything on the way through (R4 if you find bugs).

**Verification:** gates 1-3, 11-14; P11 matrix green after every commit. **Risk:** high - this is the campaign's riskiest phase; the matrix is the only thing that makes it safe. Do not start it without P11 merged.

---

### P13 - saveEvent collaborator extraction (optional)

**Goal:** promote P12's private methods into standalone classes ONLY where a method is self-contained or a second consumer exists: `app/Services/VenueUpserter.php` (from seam 1) and `app/Services/EventTicketSync.php` (seams 13-15). Everything else stays private on `EventRepo`.

**Rules:** constructor-inject or instantiate exactly as the repo's existing services do; method bodies verbatim; the P11 matrix stays green after each move. Do NOT merge `VenueUpserter` with `ConvertsLocationToVenue` (Do-NOT list - different scoping, ranking, and creation defaults). **Risk:** medium. **Manual QA:** create + edit one real event with a venue in the AP.

---

### P14 - RoleController update()/viewGuest() decomposition

**Goal:** after P7/P8 the two god methods live in smaller files - decompose them with the P12 discipline (extract-method only, seam map built in-session, 1-3 extractions per commit, suite after each).

- `update()` (~826 lines; already behind `RoleUpdateRequest` - the problem is post-validation logic): map the top-level statement blocks first (name/slug handling, image uploads, style/accent, links, payment settings, custom domain...), then extract block by block.
- `viewGuest()` (~466 lines): same treatment.

**Tests first (extends the P7 set):** custom-domain host variant, embed variant, password-protected event, curator schedule listing other schedules' events, `?date=` recurring instance; for `update()`: a full settings-save round trip pinning the resulting `roles` row column map, plus image upload (`Storage::fake`).

**Risk:** high - same class as P12; the seam map must be built by reading, not assumed. **Manual QA:** edit a schedule's settings end to end (name, image, links), reload, verify.

---

### P15 - TicketController checkout()/rsvp() decomposition

**Goal:** decompose `checkout` (~608 lines) and `rsvp` (~271 lines) inside the post-P10 `CheckoutController`, P12 discipline.

**Tests first (extends TicketingTest):** promo-code applied to a PAID sale (not just validated), volume discount (`TicketVolumeDiscount`), sold-out rejection, and each payment-method branch that is testable offline - otherwise characterize up to the redirect-out (assert the redirect URL shape and the pending `sales` row).

**Risk:** high - money paths. Extraction only, tokens preserved, no error-handling "improvements". **Manual QA:** the Appendix C Stripe test-mode purchase.

---

### P16 - Model boot() closures -> Observers

**Goal:** move `Event::boot()` (~lines 143-290: saving + deleting closures) and `Role::boot()` (~lines 194-350: saving + deleting + updating closures) into `app/Observers/EventObserver.php` / `RoleObserver.php` per rule 5(f).

**Tests first - pin each closure behavior:** markdown `*_html` fields populated on save; `*_normalized` recomputed when name is dirty; email lowercased; `starts_at` change re-keys the `sold` JSON and sale `event_date`; name-dirty resets `name_en`/translation attempts; Role email change nulls `email_verified_at` and sends verification (hosted config on, `Notification::fake`); deleting cancels active boost campaigns (Meta unconfigured -> `MetaAdsServiceFake` path, status still updated).

**Steps:** one model per commit; `#[ObservedBy(EventObserver::class)]` on the model; one closure -> one observer method, body verbatim; delete the emptied `boot()` override. Optional same phase: the Event/Role `deleting` boost-cancellation bodies are near-identical copy-paste differing in log keys - a shared `CancelsBoostCampaigns` concern is allowed ONLY if the per-model log context keys are preserved exactly.

**Verification:** gates 1-3, 11-14. **Risk:** low-medium (rule 5(f) makes it mechanical). **Manual QA:** save an event and a schedule in the AP; delete a throwaway event.

---

### P17 - FormRequest conversion wave

**Goal:** convert the inline `$request->validate()` sites that pass ALL FOUR gates of rule 5(a) - roughly half of the 124. One controller per commit, starting with `NewsletterController` (12 sites), then the post-split Role/Event/Ticket controllers.

**Tests first, per converted action lacking coverage:** happy path + one validation failure pinning the CURRENT failure semantics (redirect target + `assertSessionHasErrors` keys, or the 422 JSON shape for AJAX).

**Steps:** per site: verify the 4 gates -> create the FormRequest (rules literal verbatim, `authorize()` returns true) -> swap the type-hint -> DELETE the inline call -> keep all `$request->all()`/`input()` reads untouched. Sites failing any gate are recorded in the Campaign Log inventory as kept-inline with the reason.

**Verification:** gates 1-3, 11-14. **Risk:** medium - validation-ordering semantics; the 4 gates are the whole defense. **Manual QA:** trigger one validation failure per converted form, confirm identical message and redirect.

---

### P18 - Policy consolidation (optional)

**Goal:** fill the two stub policies (`app/Policies/EventPolicy.php`, `RolePolicy.php`) with real logic for the ~28 EXISTING `authorize()`/Gate call sites only, per rule 5(b). No mass conversion of inline checks.

**Tests first:** for each authorize() site, an allowed + a denied case pinning the current status code. **Risk:** low. **Manual QA:** none beyond the tests.

---

### F1 - event/edit JS extraction to Vite

**Goal:** stage-A verbatim relocation of the inline JS in `resources/views/event/edit.blade.php` (7,942 lines, ~3,904 of JS). IMPORTANT correction to folklore: this page is ALREADY a Vue app (`createApp` around line 4837 using the global build `public/js/vue.global.prod.js`, `@json` data passing, only ~8 Alpine `x-data` islands). The work is moving it into the Vite build, not rewriting it.

**The shared stage-A recipe (F1, F2a, F3, F4):**

1. **Behavior inventory first** (Section 3.5 layer 1) - the port-and-tick checklist.
2. New entry `resources/js/pages/<view>.js` (e.g. `event-edit.js`); add it to the `input` array in `vite.config.js`; add `@vite('resources/js/pages/event-edit.js')` in the view where the old `<script>` sat. Config edit only - no new npm deps (R8).
3. **Data island:** ALL server-interpolated values (`@json($x)`, `{{ $flag ? 'true' : 'false' }}`, `__()` strings) stay in ONE small nonce'd inline script ABOVE the entry:
   ```blade
   <script {!! nonce_attr() !!}>
     window.EventEditConfig = { event: @json($event), venues: @json($venues), i18n: { save: @json(__('messages.save')) }, ... };
   </script>
   ```
   In the moved code each former interpolation becomes a `window.EventEditConfig.x` read. Inline scripts execute in document order and Vite entries are deferred modules, so the island is guaranteed to run first.
4. **Blade `@if` around JS blocks:** emit the flag into the island and convert the guard to runtime `if (EventEditConfig.flag) {...}`; for a large plan-gated block, a conditional `@vite` of a second entry is also acceptable - pick per block and note the choice.
5. **Strict-mode audit** (L4): inventory every top-level binding shared across script-block boundaries; pin shared ones to `window.` explicitly. For this page that includes the implicit global `app = createApp(...)`.
6. **Vue import:** `import { createApp, ref } from 'vue/dist/vue.esm-bundler.js'` (L5). Remove the `vue.global.prod.js` script tag only when EVERY Vue consumer on the page has moved. `sortable.min.js` / intl-tel-input tags stay (they set globals the moved code reads).
7. Add the `data-vue-mounted="1"` marker in `mounted()` (Section 3.5 layer 3).
8. Existing `v-pre`/`<x-user-text>` guards in the markup are untouched (L3).

**F1 specifics:** move the main `createApp` block (~4836) and the shared-helper block (~197, e.g. `var use24hr`) first; a tiny scroll-guard block (~163) may stay inline with its nonce. Partial extraction is acceptable - behavior first, completeness second.

**Verification:** gates 1-3, 4, 8, 9, 10, 11-14; plus create + update an event end to end via the suite AND once in the browser. **Risk:** medium (L4/L5 class). **Manual QA:** full event-edit click-script - date/time pickers, venue selector, ticket rows add/remove, recurrence UI, image upload preview - in light + dark + one RTL locale.

---

### F2a - role/edit JS extraction to Vite

Same recipe. `resources/views/role/edit.blade.php` (7,953 lines, ~4,470 of JS, 75 jQuery + 59 Alpine occurrences - this is the jQuery-heavy page; the global `jquery.min.js` include in `layouts/app.blade.php` can only be dropped after F2b finishes and a repo-wide grep shows zero jQuery consumers). Move the main monolith block (~4358-7692) and the early block (~119-877) into `resources/js/pages/role-edit.js`. jQuery code moves AS-IS in stage A (the bundle can reference `window.$` while the global include remains). **Manual QA:** schedule-settings click-script (links editor drag/reorder, image croppers, color pickers, save/reload) in light + dark + RTL.

---

### F2b - role/edit jQuery/Alpine -> Vue per widget

**Goal:** stage B - convert the moved role-edit JS one WIDGET per commit (sortable link list, image croppers, color pickers, ...) from jQuery DOM-poking to Vue components mounted on the same markup; Alpine islands convert opportunistically (CLAUDE.md migrate-when-touching rule).

**Rules:** never mix a stage-B conversion into a stage-A move commit; one widget per commit so reverts are surgical. This is the ONE phase class verified primarily by interaction checklist rather than diffs: per widget, write the checklist (from the F2a behavior inventory), convert, tick every item in the browser, and run Dusk in CI. When the LAST jQuery consumer in the repo is gone (grep `\$(`/`jQuery(` across resources/), drop `jquery.min.js` from `layouts/app.blade.php` in its own commit. **Risk:** high - behavioral fidelity of ported handlers (delegation order, event timing). **Manual QA:** the per-widget checklists, Safari + one mobile pass.

---

### F3 - Calendar partial JS extraction (6 consumers)

**Goal:** `resources/views/role/partials/calendar.blade.php` (3,972 lines, ~2,051 of JS, Vue global build) is `@include`d by SIX views: `home`, `role/show-guest`, `role/show-guest-embed`, `role/show-admin-schedule`, `role/show-admin-availability`, `event/show-guest`. Extract its JS into ONE shared entry `resources/js/pages/schedule-calendar.js` via the stage-A recipe.

**Key point:** the six consumers pass different include parameters (`route`, `tab`, `category`, `force_mobile`, `max_events`, ...) - the PARTIAL keeps emitting its own per-page config island, so it keeps owning its data contract; the shared entry reads the island.

**Verification:** all stage-A gates, and the mount smoke + manual pass must exercise ALL SIX consumer pages, embed variant included. Dusk: calendar month navigation + event click-through on 2 consumers. **Risk:** high - blast radius; do this only after F1 or F2a has proven the pattern. **Manual QA:** click through the calendar on all six pages, light + dark, one RTL locale, plus the embed on a third-party-style test page.

---

### F4 - event/import + graphic/show JS extraction

Same stage-A recipe, smaller: `resources/views/event/import.blade.php` (~1,754 JS lines) -> `resources/js/pages/event-import.js`; `resources/views/graphic/show.blade.php` (~1,554 JS lines) -> `resources/js/pages/graphic-show.js`. No Dusk additions - these two are manual-QA pages (import wizard run with a text paste + an image; graphic designer generate/download each layout). **Risk:** medium.

---

### F5 - event/show-guest server-side detangle (optional)

**Goal:** `resources/views/event/show-guest.blade.php` (2,354 lines) is a server-side tangle: 37 `@php` blocks + 194 conditionals. Extract the `@php` logic into a view-model class (an `app/View/` directory already exists) built in the controller (post-P14 `viewGuest`), passed to the view; the template keeps identical output.

**Tests first:** normalized-HTML snapshots (Section 3.3) of a fixture event page in 4 states: tickets on / tickets off / recurring / RTL locale. **Steps:** one `@php` block per commit -> move computation into the view-model, replace the block with property reads; snapshot diff after each. **Risk:** medium. **Manual QA:** view one real event page in all 4 states.

---

### O1 - for-* marketing consolidation (stretch)

**Goal:** the 34 `resources/views/marketing/for-*.blade.php` pages (31,311 lines) share an identical skeleton (`<x-marketing-layout>` + slots + `<x-sub-audience-card>` grid) and differ only in copy/JSON-LD. Consolidate into ONE data-driven template + per-page data files.

**Hard gate:** normalized-HTML snapshots of ALL 34 pages must be BYTE-IDENTICAL before and after (the SEO copy is load-bearing and CLAUDE.md forbids altering curated WP content). If identical output cannot be achieved, ABANDON the phase - do not ship approximate.

**Steps:** snapshot all 34 -> build the template + extract one page's data -> route it through the new template -> diff -> repeat page by page (each page its own commit). Routes, controller method names, and `getDocSearchIndex()` untouched (R7). **Risk:** medium, contained entirely by the gate. **Manual QA:** eyeball 5 of 34 pages + view-source one.

---

### O2 - NoFakeEmail blocklist -> data file (optional)

**Goal:** `app/Rules/NoFakeEmail.php` (3,664 lines) is a domain blocklist as code. Move the array to `app/Rules/data/fake-email-domains.php` (a `return [...]` file), `require`d once and cached in a static property.

**Tests first:** N known-blocked domains rejected, N allowed domains pass, and the loaded array COUNT equals the current count (pin the number). **Risk:** low.

---

### O3 - AdminController + NewsletterController splits (optional)

Same controller-split recipe. `AdminController` (3,146 lines, 48 methods) -> by admin area (e.g. `AdminScheduleController`, `AdminLogController`, `AdminSupportController` - derive the grouping from the route names in-session); `NewsletterController` (1,424 lines) -> user vs admin surfaces if route analysis supports it. **Manual QA:** click one flow per moved admin area.

---

### O4 - Marketing route-block dedup (stretch)

**Goal:** routes/web.php's marketing section registers near-duplicate blocks (a flat `is_testing` block and a domain-scoped block). VERIFIED: they are NOT byte-identical (`/analytics` and `/newsletters` root redirects exist only in the domain block; `/wp/*` placement differs).

**Hard gate:** first produce an explicit diff table of the two blocks. If the table exceeds ~10 lines, SKIP the phase. Otherwise parameterize the differences into a shared registration function and prove equality with the UNSORTED per-permutation route snapshots. **Risk:** medium; L8 and L13 both apply in full.

---

## 7. Appendices

### Appendix A - Session-notes template

Keep per-phase notes (scratch, not committed) in this shape; the durable summary goes to the Campaign Log.

```
Phase: P__  Branch: refactor/p__-____  Started from: <sha>
Baselines generated: routes[ ] html[ ] graphics[ ]
Characterization commit: <sha>
Behavior inventory / seam map: ...
Skip/keep inventory (with reasons): ...
Postflight: 1[ ] 2[ ] 3[ ] 4[ ] 5[ ] 6[ ] 7[ ] 8[ ] 9[ ] 10[ ] 11[ ] 12[ ] 13[ ] 14[ ]
Bugs found: BUG-___ ...
```

### Appendix B - BUGS_FOUND.md entry template

```markdown
## BUG-007 - saveEvent creates duplicate venue when address differs only by case
- Found: 2026-07-12, Phase P11 (saveEvent characterization)
- Location: app/Repos/EventRepo.php ~840
- Repro: POST event.store with venue address "Main st" when the schedule already has "Main St"
- Current behavior: a second venue row is created
- Expected behavior: case-insensitive dedup reuses the existing venue
- Pinned by: EventSaveCharacterizationTest::test_venue_dedup_is_case_sensitive_bug007
- Severity: low | Status: preserved, not fixed
```

### Appendix C - Manual-QA menus

Automation cannot cover these (Section 3.5); present the relevant items to the owner at gate 13.

**Every phase:** the pages/flows the phase touched, opened once in light AND dark mode.

| Phase type | Owner checklist |
|---|---|
| P2 / O1 (marketing) | Visual pass on affected pages (O1: 5 of 34) + view-source spot check |
| P3 (graphics) | Open the AP graphic designer, generate + download all three layouts |
| P11-P13 (saveEvent) | Create + edit one real event with venue and tickets in the AP |
| P7-P10 / O3 (splits) | Click one flow per moved area; Carpool + Boost click-throughs if their routes moved; paid Stripe TEST-MODE purchase end to end (buy, sale row, email, check-in) when ticket/checkout code moved; Google Calendar OAuth connect/sync if its controllers moved; 2FA login if auth/profile moved |
| P16 (observers) | Save an event + a schedule; delete a throwaway event |
| P17 (FormRequests) | Trigger one validation failure per converted form - identical message and redirect |
| F-phases (JS) | The phase's full click-script (drag-drop, pickers, previews, autosave) in light + dark + one RTL locale; Safari + one mobile pass; embed page for F3 |
| Anything emailing | Send via existing test-send affordances to a real inbox; check Gmail + one dark-mode client |
| Anything date-heavy | Create an event across a DST boundary in a non-UTC schedule |

### Appendix D - Corrections to folklore

Facts verified against the tree that contradict what a fresh session might assume. Do not "fix" these assumptions back.

1. `event/edit.blade.php` is ALREADY a Vue app (global build, `createApp` ~4837); its "178 Alpine directives" were mostly Vue `@click` shorthand miscounted. Only ~8 real `x-data` islands. `role/edit.blade.php` is the jQuery-heavy page (75 uses).
2. `RoleController::update` and `TicketController::checkout` already use FormRequests (`RoleUpdateRequest`, `TicketCheckoutRequest`) - their problem is post-validation size, not inline validation.
3. `ConvertsLocationToVenue` lives at `app/Services/Concerns/` and matches against the importing user's OWN venues; `saveEvent`'s venue dedup is global with different creation defaults. Same concept, different behavior - never unify.
4. The two nexus marketing route blocks are NOT copies of each other (O4's gate exists because of this).
5. The GD graphics pipeline is fully deterministic (no randomness, no wall-clock reads) - byte-identical golden images are a valid oracle on one machine.
6. Inline `on*=` handlers: the codebase is already clean (0 real occurrences) - no cleanup phase needed.

### Appendix E - Campaign Log

Update the row when a phase merges (and add known-reds/inventories as indented notes). This table is the cross-session state - keep it accurate.

| Phase | Status | Branch | Merged sha | Date | Notes |
|-------|--------|--------|-----------|------|-------|
| P0 | done - awaiting merge | refactor/p00-test-db-isolation | | 2026-07-10 | Suite green 2x (173 tests, 612 assertions, 3 documented skips - see TEST_COVERAGE.md); NO known reds. Dev DB proven untouched (table create-times + row counts identical). Extra: `<ini name="memory_limit" value="1G"/>` added to phpunit.xml - suite peaks ~165MB and the local CLI default (128M) made bare `php artisan test` exit 255 (pre-existing OOM noted in TEST_COVERAGE.md); CI already runs 1G. |
| P1 | not started | | | | |
| P2 | tests-first done (refactor pending) | refactor/tests-quick-wins | | 2026-07-10 | Golden fixtures committed (tests/fixtures/comparison_data.json + replacement_data.json, 16+12 keys, zero env-dependent URLs) + compare_*/replace_* route smokes in MarketingDataCharacterizationTest. |
| P3 | tests-first done (refactor pending) | refactor/tests-quick-wins | | 2026-07-10 | EventGraphicStructuralTest: 3-class instantiation smoke (signature-collision fatal net) + per-layout valid-PNG/dimension checks for 1 and 3 events. Golden-image manifests stay session-local (Section 3.4). |
| P4 | not started | | | | |
| P5 | tests-first done (refactor pending) | refactor/tests-quick-wins | | 2026-07-10 | EncodedIdRoutingCharacterizationTest: 404-parity triples on event.edit + newsletter.edit. NOTE: the venue_id body-param site 404s on an invalid hash (chained findOrFail - it does NOT fall through to the no-venue path); the work order's nullable-find example was wrong for this site, trust the pinned test. |
| P6 | not started | | | | |
| P7 | not started | | | | |
| P8 | not started | | | | |
| P9 | not started | | | | |
| P10 | not started | | | | |
| P11 | done - awaiting merge | refactor/p11-saveevent-characterization | | 2026-07-10 | 43 tests / 6 classes in tests/Feature/Characterization/ (venue, members, schedule/recurrence, tickets, curators, lifecycle, custom-fields) + SavesEventsOverHttp driver trait + CreatesScheduleData helpers (createGroup/createCurator/createVenueWithAddress/createRecurringEvent/followRole). Found BUG-001 (claim_venue_ownership verified-at copy undone by Role::updating hook) - pinned, see BUGS_FOUND.md. Notable pinned quirks: non-pass tickets on one-time events store pass_usage_type='total'; new venues attach with is_accepted=null (require_approval default TRUE); the flyer upload field is flyer_image (flyer_image_url rule is vestigial); custom-field whitelisting is two-layer (FormRequest Rule::in on the form path, repo whitelist for synthesized requests). Full suite 220 passed / 3 skipped. |
| P12 | not started | | | | |
| P13 (opt) | not started | | | | |
| P14 | not started | | | | |
| P15 | not started | | | | |
| P16 | not started | | | | |
| P17 | not started | | | | |
| P18 (opt) | not started | | | | |
| F1 | not started | | | | |
| F2a | not started | | | | |
| F2b | not started | | | | |
| F3 | not started | | | | |
| F4 | not started | | | | |
| F5 (opt) | not started | | | | |
| O1 (stretch) | not started | | | | |
| O2 (opt) | tests-first done (refactor pending) | refactor/tests-quick-wins | | 2026-07-10 | NoFakeEmailCharacterizationTest: blocked/allowed domains, example.com pre-check, substring-matching semantics pinned (do NOT "fix" to exact-domain), size pinned at 3632 (source-derived until the data file exists), message pinned. |
| O3 (opt) | not started | | | | |
| O4 (stretch) | not started | | | | |

