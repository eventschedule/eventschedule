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
| Custom fields | Custom data fields on events |
| Built-in analytics | Schedule analytics dashboard |
| Sub-schedules | Group events into sub-schedules |
| Online events | Virtual event support |
| Recurring events | Day-of-week recurring patterns |
| 10 newsletters per month | Basic newsletter sending limit |

## Pro Features

Gated by `$role->isPro()`. Enterprise users also get all Pro features.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Remove Event Schedule branding | `$role->isWhiteLabeled()` / `$role->showBranding()` | White-label, removes "Powered by" |
| Ticketing & QR code check-ins | `$event->isPro()` in views and controllers | Create ticket types, scan QR codes |
| Sell online via Stripe | Tied to ticketing gate | Stripe Connect payments, no platform fees |
| Private & password-protected events | `$role->isPro()` in `event/edit.blade.php` | Restrict event visibility |
| Generate event graphics | `GraphicController`, `$role->isPro()` | Auto-generated shareable images |
| AI text processing on graphics | `GraphicController:298`, `$role->isPro()` | AI prompt for graphic text via Gemini |
| REST API access | All `Api/*Controller.php`, `$role->isPro()` | Full CRUD API for events, schedules, sales, sub-schedules |
| Event boosting with ads | `BoostController:101,202`, `$role->isPro()` | Meta Ads integration |
| Embed calendar on website | `RoleController:712`, `$role->isPro()` | iframe embed with X-Frame-Options |
| 100 newsletters per month | `$role->newsletterLimit()` | Increased sending limit |

## Enterprise Features

Gated by `$role->isEnterprise()`.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| AI event parsing | `EventController:1019`, `$role->isEnterprise()` | Parse event details from text/images via Gemini |
| Agenda scanning | `EventController:1594`, `$role->isEnterprise()` | Scan agendas to auto-create event parts |
| Save parsed event parts | `EventController:1654`, `$role->isEnterprise()` | Save AI-parsed event data |
| Email scheduling (graphic emails) | `GraphicController:142`, `$role->isEnterprise()` | Schedule automated graphic emails |
| Custom CSS styling | `RoleController:1748`, `$role->isEnterprise()` | Custom CSS on schedule pages |
| Multiple team members | `RoleController:1210,1229`, `$role->isEnterprise()` | Add/manage multiple team members |
| Newsletter management | `NewsletterController:33,45,64`, `$role->isEnterprise()` | Full newsletter creation and management UI |
| Availability management | `RoleController:2412`, `$role->isEnterprise()` | Team member availability tracking |
| 1,000 newsletters per month | `$role->newsletterLimit()` | Highest sending limit |
| Priority support | Not code-gated | Service-level commitment |

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
