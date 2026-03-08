# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Event Schedule is an open-source platform for sharing events, selling tickets, and bringing communities together. It supports both hosted (SaaS at eventschedule.com) and selfhosted deployments.

## Important Rules

- **Never run tests without asking first** - Tests will empty the database
- **Never run `npm install` without asking first** - Confirm before installing dependencies
- **Never run `composer install` without asking first** - Confirm before installing dependencies
- **Never delete migration files** - They may have already been run on production
- **Use "selfhost" not "self-host"** - Always write "selfhost" and "selfhosted" (no hyphen) except for "self-hosting"
- **Keep the sitemap up-to-date** - When adding new pages, add them to `resources/views/sitemap.blade.php`
- **Complete bento grids** - When using bento grids, ensure all cells are filled (especially the bottom right corner)
- **Align card actions to bottom** - In grids of cards/panels with varying content lengths, use `flex flex-col` on the card and `mt-auto` on the bottom element (e.g. links, buttons) so they align across cards
- **Support light and dark mode** - Always consider both light mode and dark mode when working on UI
- **Forward button at the end** - In button pairs (e.g. cancel/submit), place the forward action button at the end (right in LTR, left in RTL)
- **No co-author on commits** - Do not add "Co-Authored-By: Claude" to git commit messages
- **Never use em-dashes** - Use hyphens, "to", or "or" instead of em-dashes (—) in all written content
- **Use "schedule" not "role", "sub-schedule" not "group"** - In the code, `Role` = schedule and `Group` = sub-schedule. Always use "schedule" and "sub-schedule" in UI text and conversations, never "role" or "group"
- **MySQL only** - Only MySQL is supported; do not add SQLite compatibility to migrations or tests
- **Never use CDNs** - Always use local vendor files for JS/CSS libraries. Selfhosted users should not have the app calling external servers.
- **Never add npm dependencies** - Do not use `npm install` to add new packages. Instead, download built files manually and place them in `public/vendor/`.
- **Use `<x-link>` for inline text links** - Always use the `<x-link>` Blade component for inline text links (not navigation or buttons). It provides consistent styling, dark mode support, and an external link icon for `target="_blank"` links.
- **Use `config('app.supported_languages')` for language lists** - Never hardcode language code arrays. Always reference the centralized list in `config/app.php`.
- **Keep Help button mappings up-to-date** - When adding, removing, or moving doc pages, update the anchor map in `app/Utils/HelpUtils.php` so the admin panel Help button links to the correct docs for each section/tab
- **Match docs structure to app layout** - Documentation sections and sub-sections should mirror the app's UI structure (sections, tabs, sidebar items) where it makes sense. This keeps the Help button deep links aligned and makes docs intuitive for users navigating between the app and docs.
- **Keep `translateData` and `console.php` in sync** - Scheduled commands must be registered in both `AppController::translateData()` (hosted cron) and `routes/console.php` (selfhosted scheduler). When adding a new scheduled command, add it to both places with matching frequency.
- **Use toggle switches for boolean settings** - In the admin portal, use `<x-toggle>` (or toggle switch markup for Vue pages) for standalone boolean on/off settings. Reserve plain checkboxes for multi-select lists and "required" indicators.
- **Consistent primary action button sizing** - Primary action buttons in the AP should use `px-4 py-3 text-base` sizing to match `<x-brand-link>` / `<x-secondary-link>` components. Do not use smaller `py-2 text-sm` for standalone call-to-action buttons.
- **Keep doc search index up-to-date** - When adding, removing, or renaming doc sections, update `getDocSearchIndex()` in `MarketingController` so the docs search stays accurate
- **Never expose raw exception messages to users** - In catch blocks that handle user-facing responses, catch `QueryException` (and other system exceptions) separately and show a generic error message. Use `report($e)` to send to Sentry. Only show `$e->getMessage()` for intentional business logic exceptions.
- **Always translate new language keys** - When adding a key to non-English `resources/lang/` files, use proper translations (check existing similar keys in the file for reference), never copy the English string
- **Use bordered panels for AP warnings** - Never use plain colored text for warnings. Use `bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3` with a warning triangle SVG icon (`w-5 h-5 text-amber-600 dark:text-amber-400`) inside a flex layout
- **Never modify WP pricing feature lists or header/footer links** - The feature lists on the /pricing page and the WP header/footer navigation links are manually curated. Do not add, remove, or reorder items unless explicitly asked.
- **Use Flatpickr for date inputs** - Always use Flatpickr (already bundled via `app.js`) instead of native `type="date"` inputs. Use `dateFormat: "Y-m-d"` with `altInput: true` and `altFormat: "M j, Y"` for a human-readable display.
- **Use Vue.js, not Alpine.js or jQuery** - Always use Vue.js for JavaScript interactivity. Do not use Alpine.js directives (x-data, x-show, @click, etc.) or jQuery. When modifying files that use Alpine.js or jQuery, migrate the relevant code to Vue.js.
- **Use button components in the AP** - Use `<x-brand-button>` for primary actions (save, submit, filter), `<x-secondary-link>` for secondary navigation (back, cancel, clear), and `<x-danger-button>` for destructive actions. `<x-secondary-button>` is only for small utility buttons within forms (validate, view map, edit slug); for standalone action buttons use `<x-secondary-link>` or a plain `<button>` with `<x-secondary-link>` classes (`px-4 py-3 text-base`). Small utility buttons (e.g. "Add 30 days") may use inline styles but must use `focus:ring-[#4E81FA]` for focus rings. Settings pages (`resources/views/profile/`) use `<x-primary-button>` instead of `<x-brand-button>`. Never use inline button styles when a Blade button component exists.

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
- **Dark mode grays** (custom palette, not standard Tailwind): background `#1e1e1e`, borders/hover `#2d2d30`, text `#d1d5db`, muted text `#9ca3af`. Always use these custom values in hardcoded dark mode styles (inline CSS, `<style>` blocks, CSS overrides). Never use standard Tailwind gray hex values (e.g. `#374151`, `#111827`) - our `tailwind.config.js` overrides the entire gray scale.

## AP Design System

The AP uses a refined dark/light design language. Follow these principles when building or modifying AP components:

- **Depth through shades, not borders** - In dark mode, create visual hierarchy using subtle background shade variations (e.g. `#1A1A1A` → `#252526` → `#2d2d30`) and subtle gradients. Avoid relying on bright borders or heavy drop shadows for depth.
- **Inset shadows for active/selected states** - Active or selected items in segmented controls, tabs, and toolbars should use an inset shadow (e.g. `box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5)`) with a slightly different background shade to create a "pressed" feel.
- **Ultra-subtle separators** - Dividers between items in grouped controls should be barely visible: use `w-px` width with very low opacity (e.g. `bg-white/[0.08]` in dark mode, `bg-black/[0.08]` in light mode).
- **Generous rounded corners** - Grouped controls and containers use large border radii (`rounded-xl` to `rounded-2xl`). Individual items within groups use slightly smaller radii (e.g. `rounded-lg` to `rounded-xl`).
- **Subtle shadows** - Use `shadow-sm` for resting states and `shadow-md`/`shadow-lg` on hover. Never use heavy or colored shadows. Dark mode focus ring offset: `dark:focus:ring-offset-gray-800`.
- **Outline-style icons** - Prefer thin stroke/outline icons (not filled) in the AP. Use consistent sizing (`h-5 w-5` or `h-6 w-6`).
- **Smooth transitions** - All interactive elements should use `transition-all duration-200`. Hover effects can include subtle scale (`hover:scale-105`) and shadow changes.
- **Light mode surfaces** - Use white (`bg-white`) for cards/surfaces with subtle borders (`border-gray-200`). Use `bg-gray-50`/`bg-gray-100` for secondary surfaces and hover states.

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

**Warning: Running tests will empty the database.** Tests run against a MySQL `eventschedule` database (configured in `phpunit.xml`).

```bash
# Run all browser tests
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

## Feature Tiers (Free / Pro / Enterprise)

See `FEATURES.md` for the complete reference of which features belong to each plan tier. **Always consult `FEATURES.md`** when:
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
Cron job required: `* * * * * php artisan schedule:run`

Commands in `app/Console/Commands/`: CheckData, ExtendPlans, ReleaseTickets, Translate, UpdateApp

## Environment Variables

Key configuration in `.env`:
- `IS_HOSTED` - `true` for SaaS, `false` for selfhosted
- `APP_TESTING` - Set to `true` in test environment
- `GEMINI_API_KEY` - For AI event parsing/translation
- `REPORT_ERRORS` - Enable Sentry error reporting

## Localization

11 languages supported in `resources/lang/`: EN, ES, DE, FR, IT, PT, HE, NL, AR, ET, RU

```bash
# Check for missing translation keys across all languages
php storage/check_translations.php
```

Run this periodically when adding new translation keys to ensure all language files are in sync.
