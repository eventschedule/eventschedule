# Feature Tiers Reference

This file is the single source of truth for which features belong to each plan tier (Free, Pro, Enterprise). Use it when updating:
- Pricing page (`resources/views/marketing/pricing.blade.php`)
- Comparison/alternative pages (`resources/views/marketing/compare.blade.php`, `compare-single.blade.php`)
- Feature marketing pages (`resources/views/marketing/features.blade.php`, etc.)
- Admin portal gate checks (`$role->isPro()`, `$role->isEnterprise()`)
- Plan display page (`resources/views/role/show-admin-plan.blade.php`)

## Plan Tiers

| Tier | Price (monthly) | Price (yearly) | Code method |
|------|----------------|----------------|-------------|
| Free | $0 | $0 | default (neither `isPro()` nor `isEnterprise()`) |
| Pro | $5 | $50 | `$role->isPro()` (returns true for Pro AND Enterprise) |
| Enterprise | $15 | $150 | `$role->isEnterprise()` |

Selfhosted deployments get all Enterprise features (`isPro()` and `isEnterprise()` both return `true`).

## Free Features

All users get these features with no subscription required.

| Feature | Notes |
|---------|-------|
| Unlimited events and schedules | No caps on event or schedule count |
| Mobile-optimized, professional design | Responsive layout |
| Custom schedule URLs | Subdomain-based URLs |
| Team collaboration (single member) | One team member per schedule |
| Venue location maps | Google Maps integration |
| Google Calendar sync | Bidirectional sync |
| CalDAV sync | Standard calendar protocol |
| Fan videos & comments on events | User-generated content on events |
| Built-in analytics | Schedule analytics dashboard |
| Sub-schedules | Group events into sub-schedules |
| Online events | Virtual event support |
| Recurring events | Day-of-week recurring patterns with date exceptions (include/exclude specific dates) |
| Newsletter management | Full newsletter creation and management UI (sending limits vary by tier) |
| Embed calendar on website | iframe embed with X-Frame-Options |
| Free event registration / RSVP | Native sign-up for free events with optional capacity limits |
| 10 newsletters per month | Basic newsletter sending limit |

## Pro Features

Gated by `$role->isPro()`. Enterprise users also get all Pro features.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Remove Event Schedule branding | `$role->isWhiteLabeled()` / `$role->showBranding()` | White-label, removes "Powered by" |
| Ticketing & QR code check-ins | `$event->isPro()` in views and controllers | Create ticket types, scan QR codes |
| Sell online via Stripe | Tied to ticketing gate | Stripe Connect payments, no platform fees |
| Generate event graphics | `GraphicController`, `$role->isPro()` | Auto-generated shareable images |
| REST API access | All `Api/*Controller.php`, `$role->isPro()` | Full CRUD API for events, schedules, sales, sub-schedules |
| Webhooks | `WebhookService::dispatch()`, `$event->isPro()` | POST notifications for sales, events, check-ins |
| Event boosting with ads | `BoostController:101,202`, `$role->isPro()` | Meta Ads integration |
| Custom CSS styling | `RoleController:1748`, `$role->isPro()` | Custom CSS on schedule pages |
| Custom fields | `RoleController:1822`, `$role->isPro()` | Custom data fields on events |
| Event polls | `EventController`, `$role->isPro()` | Create polls on events, guests vote |
| Check-in dashboard | `CheckInController`, `$role->isPro()` | Real-time attendance tracking with per-ticket breakdown |
| Ticket waitlist | `WaitlistController`, `$event->isPro()` | Auto-notify when sold-out tickets become available |
| Sale notification emails | `EmailService::sendNewSaleNotification()` | Opt-in email alerts when tickets sell |
| Sales CSV export | `TicketController::exportSales()` | Export sales data with custom fields |
| 100 newsletters per month | `$role->newsletterLimit()` | Increased sending limit |

## Enterprise Features

Gated by `$role->isEnterprise()`.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| AI event parsing | `EventController:1019`, `$role->isEnterprise()` | Parse event details from text/images via Gemini |
| Agenda scanning | `EventController:1594`, `$role->isEnterprise()` | Scan agendas to auto-create event parts |
| Save parsed event parts | `EventController:1654`, `$role->isEnterprise()` | Save AI-parsed event data |
| AI text processing on graphics | `GraphicController:298`, `$role->isEnterprise()` | AI prompt for graphic text via Gemini |
| Email scheduling (graphic emails) | `GraphicController:142`, `$role->isEnterprise()` | Schedule automated graphic emails |
| Custom domains | `RoleController`, `$role->isEnterprise()` | Use your own domain for schedule |
| Private & password-protected events | `EventRepo`, `$role->isEnterprise()` | Restrict event visibility |
| Multiple team members | `RoleController:1210,1229`, `$role->isEnterprise()` | Add/manage multiple team members |
| Availability management | `RoleController:2413`, `$role->isEnterprise()` | Team member availability tracking |
| 1,000 newsletters per month | `$role->newsletterLimit()` | Highest sending limit |
| WhatsApp event creation | `WhatsAppWebhookController`, `$role->isEnterprise()` | Create events via WhatsApp messages/images with AI parsing |
| Priority support | Not code-gated | Service-level commitment |

## Selfhost-Only Features

Available only when `IS_HOSTED=false` (selfhosted deployments).

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Auto import from URLs/cities | `resources/views/role/edit.blade.php:830`, `!config('app.hosted')` | AI-powered event import from external URLs and city search |
| App update | `app/Http/Controllers/AppController.php:19`, `!config('app.hosted')` | One-click application updates |

## Newsletter Limits

Managed by `Role::newsletterLimit()` (`app/Models/Role.php`):

| Tier | Monthly limit |
|------|--------------|
| Free | 10 |
| Pro | 100 |
| Enterprise | 1,000 |
| Selfhosted (with own email settings) | Unlimited (`null`) |

## Key Code References

- **Plan tier detection**: `Role::actualPlanTier()` - `app/Models/Role.php`
- **Pro check**: `Role::isPro()` - returns `true` for Pro, Enterprise, selfhosted, testing, and admins
- **Enterprise check**: `Role::isEnterprise()` - returns `true` for Enterprise, selfhosted, testing, and admins
- **White-label check**: `Role::isWhiteLabeled()` - same as `isPro()` for branding removal
- **Branding display**: `Role::showBranding()` - inverse of white-label check
- **Newsletter limit**: `Role::newsletterLimit()` - returns limit based on tier
- **Event Pro check**: `Event::isPro()` - returns `true` if any associated schedule is Pro
- **Stripe config**: `config/services.php` lines 54-65
- **Plan management UI**: `resources/views/role/show-admin-plan.blade.php`
