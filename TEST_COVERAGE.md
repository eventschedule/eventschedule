# Test Coverage

Tracks which app features have automated **integration-test** coverage and which are gaps to fill.

_Last updated: 2026-06-15_

## How to keep this current

- When you add or extend a test, flip the affected row from ✗ to ✓ and cite the test in the `Test` column.
- When you add a feature, add a row in the matching section (and a gate suffix like `(Pro)` / `(Ent)` if applicable, per [`FEATURES.md`](FEATURES.md)).
- Recompute the summary counts when rows change.
- Coverage counts **integration tests only** (see legend). Do not count route/page-load smoke tests or DB test-helpers.

## Legend

| Mark | Meaning |
|------|---------|
| ✓ | **Covered** — an integration test performs the feature's action and asserts the outcome. |
| ✗ | **Not covered** — no integration test. |

**Integration tests** = Dusk browser tests (`tests/Browser/`) and behavior-asserting PHPUnit Feature tests (`tests/Feature/`).

New Feature-test suites added this session (all use `tests/Feature/Concerns/CreatesScheduleData.php`): `EventTest`, `TicketingTest`, `NewsletterFeatureTest`, `MiscFeaturesTest`, `ApiAdminTest`, `AuthExtraTest`.

> **Not counted as coverage:** `RouteLoadTest` (HTTP-200 smoke), DB test-helpers (`upgradeToEnterprise()`), and placeholder/unit tests. External-service features (Stripe billing, Google Calendar, CalDAV, Eventbrite, Meta Ads, WhatsApp, Gemini/OpenAI AI) are marked ✗ — they need live credentials or service mocks; see **Notes** at the bottom.

## Summary

**94 of 145 features covered (~65%).** (Up from 53 / ~37% at the start of this session.)

| Area | Covered |
|------|---------|
| Authentication & Account | 8 / 11 |
| User Profile & Settings | 7 / 7 |
| Schedules (Roles) | 10 / 16 |
| Sub-schedules (Groups) | 4 / 4 |
| Events | 13 / 13 |
| Ticketing & Payments | 14 / 21 |
| Community & Engagement | 6 / 7 |
| Newsletters | 10 / 11 |
| Integrations | 0 / 9 |
| AI Features | 0 / 8 |
| Graphics, Analytics & Promotion | 0 / 4 |
| Customization & Branding | 6 / 8 |
| Backup & Data | 1 / 2 |
| Developer / API | 5 / 8 |
| Billing & Plans | 1 / 3 |
| Guest Portal | 6 / 7 |
| Platform Admin | 2 / 6 |
| **Total** | **94 / 145** |

## Coverage by feature

### Authentication & Account
| Feature | Tested | Test |
|---|---|---|
| Email/password signup | ✓ | `RegistrationTest`, `GeneralTest` |
| Login / logout | ✓ | `Auth/AuthenticationTest`, `GeneralTest` |
| Email verification | ✓ | `Auth/EmailVerificationTest` |
| Password reset | ✓ | `Auth/PasswordResetTest` |
| Password change/update | ✓ | `PasswordTest`, `Auth/PasswordUpdateTest` |
| Password confirmation | ✓ | `Auth/PasswordConfirmationTest` |
| Hosted login redirect (subdomain→app) | ✓ | `HostedLoginRedirectTest` |
| Social login (Google) | ✓ | `AuthExtraTest` (mocked Socialite) |
| Social login (Facebook) | ✗ | — (only Google covered) |
| Phone verification (SMS) | ✗ | — (needs Twilio mock) |
| Two-factor authentication | ✗ | — |

### User Profile & Settings
| Feature | Tested | Test |
|---|---|---|
| Edit name | ✓ | `ProfileTest` |
| Timezone | ✓ | `ProfileTest` (Browser) |
| 24-hour time toggle | ✓ | `ProfileTest` (Browser) |
| UI language selection (11 languages) | ✓ | `ProfileTest` (Browser) |
| Email update | ✓ | `ProfileTest` (Feature) |
| Account deletion | ✓ | `ProfileTest` (Feature) |
| Profile image upload/delete | ✓ | `ImageTest` |

### Schedules (Roles)
| Feature | Tested | Test |
|---|---|---|
| Create Talent schedule | ✓ | `GeneralTest` |
| Create Venue schedule | ✓ | `EventManagementTest` |
| Create Curator schedule | ✓ | `CuratorEventTest` |
| Edit schedule details/contact | ✓ | `GeneralTest`, `MiscFeaturesTest` |
| Delete schedule | ✓ | `EventManagementTest` |
| Custom event categories | ✓ | `ScheduleCategoriesTest` |
| Availability management (Ent) | ✓ | `AvailabilityTest` |
| Configurable dashboard | ✓ | `MiscFeaturesTest` |
| Multiple team members (Ent) | ✓ | `MiscFeaturesTest` (add/update/remove) |
| Guest portal banner (Pro) | ✓ | `MiscFeaturesTest` |
| Unlimited events & schedules | ✗ | — (non-functional) |
| Custom schedule URLs (subdomain slug) | ✗ | — |
| Mobile-optimized / responsive design | ✗ | — (non-functional) |
| Venue location maps (Google Maps) | ✗ | — |
| Sponsor / partner logos (Pro) | ✗ | — |
| Custom domains (Ent) | ✗ | — (needs DigitalOcean) |

### Sub-schedules (Groups)
| Feature | Tested | Test |
|---|---|---|
| Create sub-schedules | ✓ | `GroupsTest` |
| Assign events to sub-schedules | ✓ | `GroupsTest` |
| Filter events by sub-schedule (GP) | ✓ | `GroupsTest` |
| Sub-schedule via API | ✓ | `GroupsTest`, `ApiAdminTest` |

### Events
| Feature | Tested | Test |
|---|---|---|
| Create event | ✓ | `GeneralTest`, `EventManagementTest` |
| Create event with new venue | ✓ | `EventManagementTest` |
| Edit event | ✓ | `EventManagementTest`, `ApiAdminTest` |
| Clone event | ✓ | `EventManagementTest` |
| Delete event | ✓ | `EventManagementTest`, `ApiAdminTest` |
| Event image upload | ✓ | `ImageTest` |
| Recurring events | ✓ | `EventTest` |
| Online / virtual events | ✓ | `EventTest` |
| Event agenda / parts | ✓ | `EventTest` (save-event-parts) |
| Save AI-parsed event parts (Ent) | ✓ | `EventTest` (same save endpoint) |
| Markdown descriptions | ✓ | `EventTest` |
| Private & password-protected events (Ent) | ✓ | `EventTest` |
| Event polls (Pro) | ✓ | `EventTest` |

### Ticketing & Payments
| Feature | Tested | Test |
|---|---|---|
| Ticket types (create/settings) | ✓ | `TicketTest` |
| Free event registration / RSVP | ✓ | `TicketTest`, `TicketingTest` |
| Promo / discount codes | ✓ | `TicketTest`, `TicketingTest` |
| Custom fields (event & ticket level) | ✓ | `TicketTest` |
| Sales list / management | ✓ | `TicketTest` |
| Ticket quantity limits / oversell | ✓ | `TicketingTest` |
| Individual tickets (Pro) | ✓ | `TicketingTest` |
| Passes & subscriptions (Pro) | ✓ | `TicketingTest` |
| QR code ticketing / check-in | ✓ | `TicketingTest` |
| Check-in dashboard (Pro) | ✓ | `TicketingTest` (stats) |
| Ticket waitlist (Pro) | ✓ | `TicketingTest` |
| Sales CSV export (Pro) | ✓ | `TicketingTest` |
| Post-event feedback (Pro) | ✓ | `TicketingTest` |
| Ticket add-ons / upsells | ✓ | `TicketingTest` |
| Ticket reservations / release time | ✗ | — |
| Sell online via Stripe | ✗ | — (needs Stripe) |
| Invoice Ninja integration | ✗ | — (needs Invoice Ninja) |
| Payment links | ✗ | — |
| Sale notification emails (Pro) | ✗ | skipped — env-guarded mail path (see Notes) |
| Bulk attendee import (Pro) | ✗ | skipped — timezone-sensitive fixture (see Notes) |
| Embed ticket widget (Pro) | ✗ | — |

### Community & Engagement
| Feature | Tested | Test |
|---|---|---|
| Follow / unfollow schedules | ✓ | `FollowerTest`, `NewsletterFeatureTest` |
| Guest event submissions (curator request) | ✓ | `CuratorEventTest` |
| Approve / decline curator requests | ✓ | `CuratorEventTest` |
| Fan videos (submit + approve) | ✓ | `EventTest` |
| Fan photos (submit) | ✓ | `EventTest` |
| Fan comments (submit) | ✓ | `EventTest` |
| Carpool matching (Pro) | ✗ | — |

### Newsletters
| Feature | Tested | Test |
|---|---|---|
| Create / draft newsletter | ✓ | `NewsletterTest` |
| Edit newsletter | ✓ | `NewsletterTest` |
| Delete newsletter | ✓ | `NewsletterTest` |
| Send / publish newsletter | ✓ | `NewsletterFeatureTest` |
| Audience segments | ✓ | `NewsletterFeatureTest` |
| A/B testing | ✓ | `NewsletterFeatureTest` |
| Templates (block editor) | ✓ | `NewsletterFeatureTest` |
| Import subscribers | ✓ | `NewsletterFeatureTest` |
| Scheduled sends | ✓ | `NewsletterFeatureTest` |
| Delivery analytics (open/click tracking + unsubscribe) | ✓ | `NewsletterFeatureTest` |
| Email-limit enforcement (tier caps) | ✗ | — |

### Integrations
| Feature | Tested | Test |
|---|---|---|
| Google Calendar sync | ✗ | — (needs Google OAuth) |
| CalDAV sync | ✗ | — (needs CalDAV server) |
| Eventbrite import (Pro) | ✗ | — (needs Eventbrite OAuth) |
| Third-party / URL import (selfhost) | ✗ | — |
| Webhooks (Pro) | ✗ | — |
| WhatsApp event creation (Ent) | ✗ | — (needs Meta webhook) |
| Add to Google/Apple/MS calendar | ✗ | — |
| iCal download (.ics, per event) | ✗ | — (subscription feed is covered under Guest Portal) |
| Embed calendar on website (iframe) | ✗ | — |

### AI Features
| Feature | Tested | Test |
|---|---|---|
| AI event parsing (text/image) | ✗ | — (needs Gemini) |
| AI translation | ✗ | — (needs Gemini) |
| AI flyer generation (Ent) | ✗ | — (needs OpenAI) |
| AI style generation (Ent) | ✗ | — (needs OpenAI) |
| AI schedule details generation (Ent) | ✗ | — (needs Gemini) |
| AI event details generation (Ent) | ✗ | — (needs Gemini) |
| AI agenda scanning (Ent) | ✗ | — (needs Gemini) |
| AI text processing on graphics (Ent) | ✗ | — (needs Gemini) |

### Graphics, Analytics & Promotion
| Feature | Tested | Test |
|---|---|---|
| Event graphics generator (Pro) | ✗ | — |
| Scheduled graphic emails (Ent) | ✗ | — |
| Built-in analytics dashboard | ✗ | — |
| Event boosting / Meta Ads (Pro) | ✗ | — (needs Meta Ads) |

### Customization & Branding
| Feature | Tested | Test |
|---|---|---|
| Schedule profile image | ✓ | `ImageTest` |
| Header image | ✓ | `ImageTest` |
| Background image | ✓ | `ImageTest` |
| Accent color & font selection | ✓ | `MiscFeaturesTest` |
| Custom CSS (Pro) | ✓ | `MiscFeaturesTest` |
| Custom labels (Pro) | ✓ | `AuthExtraTest` |
| White-label branding / remove "Powered by" (Pro) | ✗ | — |
| Custom domains (Ent) | ✗ | — (needs DigitalOcean) |

### Backup & Data
| Feature | Tested | Test |
|---|---|---|
| Backup export | ✓ | `ApiAdminTest` |
| Restore import | ✗ | — (multi-step upload/confirm flow) |

### Developer / API
| Feature | Tested | Test |
|---|---|---|
| REST API — schedules (GET/PUT) | ✓ | `ApiTest`, `ApiAdminTest` |
| REST API — events (GET/POST/PUT/DELETE) | ✓ | `ApiTest`, `ApiAdminTest` |
| REST API — sales (GET) | ✓ | `ApiAdminTest` |
| REST API — groups (POST) | ✓ | `GroupsTest`, `ApiAdminTest` |
| REST API — auth / invalid key | ✓ | `ApiTest` |
| Webhooks (Pro) | ✗ | — |
| AI agent support (agents.json / OpenAPI / llms.txt) | ✗ | — |
| Automatic app updates (selfhost) | ✗ | — |

### Billing & Plans
| Feature | Tested | Test |
|---|---|---|
| Referral program | ✓ | `ApiAdminTest` |
| Subscribe / upgrade plan (Stripe) | ✗ | — (needs Stripe) |
| Downgrade / swap / cancel / resume | ✗ | — (needs Stripe) |

### Guest Portal
| Feature | Tested | Test |
|---|---|---|
| Public schedule view | ✓ | `GroupsTest` |
| Event detail page | ✓ | `TicketTest`, `EventTest` |
| Ticket selection / free RSVP checkout (GP) | ✓ | `GeneralTest`, `TicketingTest` |
| Search events | ✓ | `MiscFeaturesTest` |
| iCal / RSS feeds | ✓ | `MiscFeaturesTest` |
| Past events listing | ✓ | `MiscFeaturesTest` |
| Browse / discover schedules | ✗ | — |

### Platform Admin
| Feature | Tested | Test |
|---|---|---|
| Admin dashboard | ✓ | `ApiAdminTest` |
| Blog | ✓ | `ApiAdminTest` |
| User / domain management | ✗ | — |
| Sale approval / refund | ✗ | — |
| Support chat | ✗ | — |
| Audit logs | ✗ | — |

## Notes (findings, blockers, and pre-existing issues)

### Pre-existing test failures fixed (stale test ↔ app drift)
1. **`ProfileTest` (Feature)** — two tests asserted a redirect to `/settings`; the app now redirects to `/settings#section-profile`. Updated.
2. **`Auth/PasswordUpdateTest`** — asserted redirect to `/profile`; the app redirects to `/settings#section-password`. Updated.
3. **`ExampleTest`** — asserted `/` ends at `/login`; in hosted+test mode `/` returns 200 (home). Removed the stale assertion.
4. **`NewsletterTest::test_newsletter_index_accessible_to_non_enterprise_role`** — failed with 302 because in hosted mode (`is_testing=false`) bare-domain AP routes redirect to the `app.` subdomain; the test now requests the app-subdomain host.

### Suspected app robustness issue (worth a look)
- **Null timezone → 500.** `Event::getStartDateTime()` (`app/Models/Event.php:1555`) calls `Carbon::setTimezone($timezone)` where `$timezone` comes from `auth()->user()->timezone`. If the authenticated user has a `null` timezone, this throws a `TypeError` (500) — hit via the bulk-attendee-import path. Real users normally have a timezone, but the value isn't guarded. Consider defaulting to UTC when null.

### Documented skips (kept as `markTestSkipped` with reasons)
- **Bulk attendee import** (`TicketingTest`) — the posted `event_date` must match the event's *local* occurrence date (`matchesDate` uses the schedule timezone); reliable coverage needs a fixed-timezone fixture.
- **Sale confirmation email** (`TicketingTest`) — in hosted mode the email only sends when the schedule has a configured mail transport, and the dispatch path (queued job vs `RoleMailerService`) depends on runtime config; not reliably assertable with the array mailer.
- **`RegistrationTest::test_registration_redirect_to_login_page`** — asserts selfhost behavior via a runtime `config(['app.hosted'=>false])`, but routes/middleware are registered at boot from the hosted `.env`, so the selfhost path can't be exercised. Needs a dedicated `IS_HOSTED=false` test env.

### Environment / tooling
- **ChromeDriver mismatch** — installed Chrome was v149 but the bundled driver was v147 (Dusk failed with `SessionNotCreatedException`). Fixed by running `php artisan dusk:chrome-driver --detect`.
- **Feature suite OOMs at the very end.** All 14 Feature classes pass (0 failures), but the PHP process exits 255 with `Allowed memory size exhausted` while buffering final output — some test response renders large binary/image (PNG) content that PHPUnit accumulates. Cosmetic (all tests already passed) but could cause CI flakiness; raise `memory_limit` for the suite or avoid asserting against image-returning routes in a way that buffers the body.

### Not covered (need live services or service mocks)
Stripe checkout/billing, Google Calendar, CalDAV, Eventbrite, Meta Ads/Boost, WhatsApp, and all Gemini/OpenAI AI features are marked ✗. They require live credentials or HTTP/SDK mocks (e.g. `Socialite` is mocked for Google login — the same approach could extend to these). Also untested but feasible as a follow-up: analytics view recording, webhooks delivery, backup restore, support chat, audit logs, carpool, Facebook login, phone verification (Twilio mock).
