# Test Suite Overview

The active test suite lives under `tests/Feature` and `tests/Unit`. Legacy suites are preserved under `tests/legacy` but excluded from the default PHPUnit configuration.

## Running the tests

```
composer test
```

This command runs `php artisan test` against an in-memory SQLite database. Environment defaults are set in `phpunit.xml` (`APP_ENV=testing`, `APP_DEBUG=true`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`).

## What is covered
- Authentication: sign up, login/logout, password reset flow.
- User management: admin-only creation and access gating for the settings area.
- RBAC core: authorization caching and scoped resource visibility for venues/talent.

## Notes and caveats
- Each test refreshes the database to avoid cross-test coupling.
- External integrations (Google, Stripe, etc.) are intentionally not exercised to keep the suite deterministic.
- When adding time-sensitive logic, prefer `Carbon::setTestNow()` and explicit seeding over implicit `now()` calls.
