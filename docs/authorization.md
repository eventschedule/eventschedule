# Authorization & RBAC

Event Schedule ships with a single-tenant role-based access control (RBAC) system that enforces the same permission model for the web UI and API. Every authenticated request is evaluated against cached permission sets so we can return `401/403` consistently, hide unauthorized actions, and keep checks O(1).

## Goals

- **Least-privilege defaults.** Users only inherit the abilities assigned to their system role(s).
- **Auditable changes.** Updates to global settings, login/logout activity, refunds, and check-ins are logged to `audit_logs` with IP and user-agent metadata.
- **Unified enforcement.** Routes opt into the `ability:<permission-key>` middleware (see `routes/api.php` and `routes/web.php`) so the same permission keys protect both APIs and Blade controllers.
- **Deterministic migrations.** The `AuthorizationSeeder` creates the roles, permissions, and initial mappings and assigns the first user to the `Owner` role when the `user_roles` table is empty.

## Data model

| Table | Purpose |
| --- | --- |
| `auth_roles` | System roles such as SuperAdmin, Owner, BoxOffice, etc. |
| `permissions` | Canonical permission keys (e.g., `events.publish`, `tickets.refund`). |
| `role_permissions` | Pivot table mapping system roles to permissions. |
| `user_roles` | Pivot table mapping users to system roles. |
| `audit_logs` | Append-only log of authentication and operational actions. |

### Permission keys

`config/authorization.php` documents the cache and retention settings. `database/seeders/AuthorizationSeeder.php` seeds the following keys (abbreviated list):

- `settings.manage`, `users.manage`, `roles.manage`, `impersonate.use`
- CRUD-style keys for `events`, `venues`, `talent`, `curators`, `tickets`, `media`, and `wallet`
- Operational keys such as `tickets.refund`, `tickets.checkin`, `orders.view`, `orders.export`, `reports.export`

### Role matrix

| Role | Highlights |
| --- | --- |
| **SuperAdmin** | Full platform access plus impersonation. |
| **Owner** | Full CRUD on every resource (no impersonation). |
| **Admin** | Manage settings, users, and all records. |
| **Curator** | Create/update/publish events, venues, talent, and curators. Export reports. |
| **Editor** | Create/update content but cannot publish/delete. |
| **BoxOffice** | View events, sell/refund tickets, export orders/reports, issue wallet passes. |
| **Door** | View events/orders, scan/check in tickets, validate wallet passes. |
| **Viewer** | Read-only access to public resources. |

The `AuthorizationService` warms a role→permission cache and exposes helper methods that `User::hasPermission()` uses throughout the codebase. The custom `ability` middleware (see `app/Http/Middleware/EnsureAbility.php`) enforces these keys on routes like:

```php
Route::middleware(['auth', 'ability:settings.manage'])->group(function () {
    // settings + admin routes
});
```

## Auditing

- **Login/logout**: `HandleSuccessfulLogin` and `HandleLogout` listeners capture each session transition.
- **Settings**: Every mutating action inside `SettingsController` calls `auditSettingsChange()` with metadata describing which keys were touched.
- **Orders/tickets**: Refunds, cancellations, deletions, mark-paid operations, and QR check-ins are written to `audit_logs` via `AuditLogger` in `TicketController`.
- **Retention**: `config/authorization.php` exposes `AUDIT_LOG_RETENTION_DAYS`; use `php artisan audit:prune` to purge older rows.

## CLI helpers

Use `php artisan authorization:assign-role {user} {role} [--remove]` to promote/demote users. The command accepts a user ID or email address and keeps the permission cache fresh by warming/flushing entries via `AuthorizationService`.

## UI cues

- The authenticated navigation sidebar now surfaces the user's system roles as badges.
- A dedicated Access Denied page (`resources/views/errors/403.blade.php`) explains why an action was blocked and links to a "Request access" CTA.
- Unauthorized actions in controllers fall back to `abort(403)` with the same translated messaging so the error page is consistently rendered.

For a complete list of permission keys and their descriptions, consult `database/seeders/AuthorizationSeeder.php`. Each key in that seeder maps directly to the `ability:<permission>` middleware and `User::hasPermission()` helper.
