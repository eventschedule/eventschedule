# Testing Strategy

## Existing Test Inventory
- All historical tests have been moved to `tests/legacy/` to avoid executing unreliable suites (Browser, Feature, Unit, and legacy Dusk scaffolding).
- Legacy tests covered a broad set of console commands, media library flows, and ticket sales but depended on shared state, external services (Google APIs, Stripe), and lacked consistent database isolation, making them flaky.

## Deterministic Test Principles
- Run against an isolated, in-memory SQLite database (configured in `phpunit.xml`).
- Every feature and unit test uses `RefreshDatabase` to reset schema and data between tests.
- No external network calls or randomness beyond Faker defaults; time-sensitive code should be wrapped with controlled timestamps when introduced.
- Permissions and roles are created via factories to avoid hidden fixtures.

## Coverage Priorities
- **Authentication:** registration, login/logout, password reset flows.
- **User management:** admin-only creation/update/delete paths, validation expectations, and status/role assignment.
- **RBAC:** authorization caching, ability middleware behavior, and scoped access to venues/curators/talent.
- **Resources:** basic CRUD and visibility rules for roles (venues, talent, curators) and their association to users.
- **Events:** creation/update rules and associations (venue/role links, scheduling integrity).
- **Validation:** server-side validation for key forms and API responses.

## Test Suite Layout
- `tests/Feature/` — end-to-end HTTP behavior (auth flows, user management, RBAC enforcement).
- `tests/Unit/` — domain logic, model relationships/scopes, authorization caching.
- `tests/legacy/` — archived flaky suites; not executed by default.

## Data Setup Conventions
- Prefer model factories for all entities (User, SystemRole, Permission, Role, Event).
- Use `actingAs` for authenticated scenarios and attach permissions through `SystemRole` + `Permission` pivots.
- Keep assertions high-signal: status codes, redirects, database state (`assertDatabaseHas`), and authorization outcomes (403s).

## Future Enhancements
- Add API contract tests for any JSON endpoints once stabilized.
- Extend feature coverage for event scheduling workflows (cloning, ticket exports) after decoupling external integrations.
- Introduce time-mocked tests (`Carbon::setTestNow`) for scheduling/availability logic when added to the suite.
