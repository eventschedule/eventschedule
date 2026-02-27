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
