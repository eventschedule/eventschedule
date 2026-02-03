# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Event Schedule is an open-source platform for sharing events, selling tickets, and bringing communities together. It supports both hosted (SaaS at eventschedule.com) and selfhosted deployments.

## Important Rules

- **Never run tests without asking first** - Tests will empty the database
- **Never run `npm install` without asking first** - Confirm before installing dependencies
- **Never run `composer install` without asking first** - Confirm before installing dependencies
- **Never delete migration files** - They may have already been run on production
- **Use "selfhost" not "self-host"** - Always write "selfhost" and "selfhosted" (no hyphen)
- **Keep the sitemap up-to-date** - When adding new pages, add them to `resources/views/sitemap.blade.php`
- **Complete bento grids** - When using bento grids, ensure all cells are filled (especially the bottom right corner)
- **Support light and dark mode** - Always consider both light mode and dark mode when working on UI
- **No co-author on commits** - Do not add "Co-Authored-By: Claude" to git commit messages
- **Never use em-dashes** - Use hyphens, "to", or "or" instead of em-dashes (—) in all written content
- **Use "schedule" not "role", "sub-schedule" not "group"** - In the code, `Role` = schedule and `Group` = sub-schedule. Always use "schedule" and "sub-schedule" in UI text and conversations, never "role" or "group"

## Terminology

- **WP** - Marketing site (from WordPress acronym)
- **AP** - Admin portal
- **CP** - Guest portal / Client portal
- **Role** (code) = **schedule** (UI) - The `Role` model represents a schedule. Always refer to it as "schedule" in text
- **Group** (code) = **sub-schedule** (UI) - The `Group` model represents a sub-schedule. Always refer to it as "sub-schedule" in text

## Brand Colors

- **WP primary blue:** `#4E81FA`
- **WP gradient:** `#4E81FA` -> `#0EA5E9` -> `#22D3EE`
- Shared `.text-gradient` class is defined in `resources/css/marketing.css`
- Never use purple/violet/indigo/fuchsia/pink as WP brand colors
- Icon accent colors (on sub-audience-cards) are decorative and exempt

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

**Warning: Running tests will empty the database.**

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

9 languages supported in `resources/lang/`: EN, ES, DE, FR, IT, PT, HE, NL, AR

```bash
# Check for missing translation keys across all languages
php storage/check_translations.php
```

Run this periodically when adding new translation keys to ensure all language files are in sync.
