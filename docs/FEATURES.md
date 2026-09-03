# Feature Tiers Reference

This file is the single source of truth for which features belong to each plan tier (Free, Pro, Enterprise). Use it when updating:
- Pricing page (`resources/views/marketing/pricing.blade.php`)
- Comparison/alternative pages (`resources/views/marketing/compare.blade.php`, `compare-single.blade.php`)
- Feature marketing pages (`resources/views/marketing/features.blade.php`, etc.)
- Admin portal gate checks (`$role->isPro()`, `$role->isEnterprise()`)
- Plan display page (`resources/views/role/show-admin-plan.blade.php`)

## Plan Tiers

> Amounts are eventschedule.com's defaults. A selfhosted or white-label platform sets its own via
> `STRIPE_PRICE_*_AMOUNT`, and the currency symbol via `PLATFORM_CURRENCY` / `/admin/settings`.

| Tier | Price (monthly) | Price (yearly) | Code method |
|------|----------------|----------------|-------------|
| Free | $0 | $0 | default (neither `isPro()` nor `isEnterprise()`) |
| Pro | $5 | $50 | `$role->isPro()` (returns true for Pro AND Enterprise) |
| Enterprise | $15 | $150 | `$role->isEnterprise()` |

Prices are set by `STRIPE_PRICE_MONTHLY_AMOUNT` / `STRIPE_PRICE_YEARLY_AMOUNT` /
`STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT` / `STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT`, with the
defaults above in `config/services.php`. Never hardcode a price in a view: the marketing site
and the referral page read them from a view composer in `AppServiceProvider`, and
`tests/Feature/MarketingPriceTest.php` fails the build if a literal creeps back in.

Existing subscribers keep whatever price their Stripe subscription was created at - Cashier
pins the price on the subscription item, so a change here only affects new checkouts.

Selfhosted deployments get all Enterprise features (`isPro()` and `isEnterprise()` both return `true`).

## Free Features

All users get these features with no subscription required.

| Feature | Notes |
|---------|-------|
| Unlimited events and schedules | No caps on event or schedule count |
| Event visibility (Public & Draft) | Publish events publicly or keep them members-only as a Draft; set a per-schedule default for new events |
| Mobile-optimized, professional design | Responsive layout |
| Custom schedule URLs | Subdomain-based URLs |
| Team collaboration (single member) | One team member per schedule |
| Schedule ownership transfer | Hand a schedule and everything on it to another account (`ScheduleTransferService`). The recipient accepts by signing in as the invited address; `events.user_id` re-points so ticket revenue follows, and on hosted the previous owner's subscription is cancelled at the end of the period already paid for |
| Venue location maps | Google Maps integration |
| Google Calendar sync | Bidirectional sync; per-schedule delete-sync action (keep, mark cancelled, or delete a local event when it is deleted in the external calendar) |
| Outlook Calendar sync | Microsoft 365 / Graph two-way sync, optional Teams meeting links; shares the per-schedule delete-sync action |
| CalDAV sync | Standard calendar protocol |
| Fan videos, photos & comments on events | User-generated content on events (25 photo limit on free tier). Attendees can submit with just a name and email; a per-schedule toggle can require an account instead. All submissions go through an approval queue |
| Built-in analytics | Schedule analytics dashboard |
| Configurable dashboard | Customize which panels appear on the dashboard |
| Sub-schedules | Group events into sub-schedules |
| Multi-event cart | A guest buys tickets to several of a schedule's events in one checkout, paid as a single amount (`sales.order_id`; `TicketController::checkout()` legs). Untiered, like ticket selling itself: the per-month cap on PAID tickets still applies per the Pro row below |
| Curator event sources | A curator lists talent/venue schedules and every event they publish, past and upcoming, is linked onto it automatically (`role_sources` + `CuratorSourceService`; reconciled by `app:sync-curator-sources`). The pull-side counterpart to the existing `roles.default_curator_ids` push, and ungated for the same reason |
| Online events | Virtual event support |
| Recurring events | Day-of-week recurring patterns with date exceptions (include/exclude specific dates) |
| Newsletter management | Full newsletter creation and management UI (sending limits vary by tier) |
| Email subscribers (audience capture) | `role_subscribers` + `App\Services\AudienceResolver`. A signed-out visitor gives the schedule an email address from the sign-up panel, the follow modal or a checkout tick box, and becomes a recipient without an account. Double opt-in from the panel, single opt-in at checkout (the receipt already proves the address). Unlimited on every tier, and listed on the Followers tab. Works on selfhost, where the panel is the only capture surface: the Follow modal is hosted-only |
| Automatic new-event announcements | `app:send-event-announcements`. When a schedule publishes public events, its CONFIRMED subscribers get one digest covering the batch, floored at `usage.audience_announcement_min_hours` (default 72) per schedule. This is what the sign-up copy promises, so it is ungated and does NOT count against the newsletter allowance below - the cadence floor is what bounds it. Still subject to `Role::canSendAudienceMail()`, so an unverified schedule on the shared platform mailer reaches at most `usage.audience_mail_unverified_max_recipients` (default 50). Owner opt-out per schedule at Settings > Notifications, default on. Account followers are NOT included; they are reached only by a newsletter |
| Embed calendar on website | iframe embed with X-Frame-Options |
| Free event registration / RSVP | Native sign-up for free events with optional capacity limits. Unlimited on every tier, and never counted against the paid-ticket allowance below. The RSVP variants of the waitlist, per-guest individual registration and the `?rsvp=true` embed widget are free too - they always have been |
| Sell tickets (25 paid tickets per month) | `Role::ticketSaleLimit()`. Create ticket types and take payment, including online via Stripe Connect with **no platform fee**, the same as Pro. Capped at 25 paid tickets per calendar month per schedule, with a per-owner backstop across a user's schedules. Free RSVPs, zero-price tickets, add-ons and appointment bookings never count. Cash sales are counted but never blocked, and the cap never applies to an event starting within 48 hours. The first paid sale on each event always notifies the organizer |
| Payment gateways (Stripe, Payfast, payment link, cash) | `config/payments.php` + `App\Services\Payments\PaymentGatewayManager`. Every gateway is available on every tier - there is NO `isPro()` check anywhere in `app/Services/Payments/`, and the connect route is gated on auth only. Connect one per account in Settings > Payment Methods and pick per event. A selfhost operator can instead supply one account for the WHOLE install in `.env`. For Stripe that is `STRIPE_PLATFORM_SECRET` and it is the only rail selfhost has (`User::canAcceptStripePayments()`) - there is no per-owner Stripe account on selfhost to override. For Payfast it is `PAYFAST_MERCHANT_ID`/`_KEY`/`_PASSPHRASE` (`PayfastGateway::platformCredentials()`), and there it is a default rather than an override: an owner who connected their own keeps using it. `DEFAULT_PAYMENT_METHOD` sets what a new event starts on instead of cash, honoured only where the gateway is actually usable. **Payfast** settles in South African rand only, so it is offered only on ZAR events, and a Payfast event cannot join the multi-event cart or use installments. Invoice Ninja is the one gateway that IS Pro - see below |
| Appointment booking (1 appointment type) | `Role::appointmentTypeLimit()`. Fully featured otherwise: weekly hours, per-date overrides, buffers, approvals, and payment via Stripe / payment URL / cash. A schedule that lapses from Pro keeps every type it created; only the oldest bookable one stays bookable, and the rest return on upgrade |
| QR code scanning at the door | `TicketController::scan()` / `scanned()`, gated only by `User::canScanEvent()` (a permission check for owners, admins and viewers - it has NO plan check). The "Scan Ticket" button on the Sales tab renders unconditionally. Scan the QR on any ticket or registration, including the 25 paid tickets a month the free plan sells; each ticket admits once and a re-scan warns. The live **Check-in dashboard** (running count and per-ticket-type breakdown) is the Pro half - see below |
| iCal download | Download .ics files for individual events and recurring event dates |
| Fan photos on events (25 per schedule) | User-submitted photos with approval workflow; upgrade prompt at limit |
| Event cloning | Duplicate an existing event as a starting point for a new one |
| Venue logo wall header | Banner header option showing logos of venues (talents for venue schedules) from approved public events; drag-reorderable on the edit page |
| Backup & restore | Export and import schedule data with optional images |
| 10 newsletter emails per month | Basic newsletter email sending limit (counts each recipient as one email). Automatic new-event announcements are separate and do not draw on it |
| AI event parsing | `EventController::parse`, daily limit | Parse event details from text/images via Gemini (10/day free, 50/day pro, 100/day enterprise) |

## Pro Features

Gated by `$role->isPro()`. Enterprise users also get all Pro features.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Remove Event Schedule branding | `$role->isWhiteLabeled()` / `$role->showBranding()` | White-label, removes "Powered by" from all seven surfaces. The corner credit chip is decided separately by `Role::creditChipReason()`: on any install that is not eventschedule.com - a plain selfhost or an operator's own platform - it is the AAL attribution owed by whoever redistributes the software, so no plan buys it off. It stands down wherever the dark strip already renders, so on an operator's platform it lands on the tiers they charge for and their free tier shows the strip alone. On the nexus the one case is an Enterprise plan an admin granted by hand (`roles.plan_source = 'admin'`, no active Stripe subscription); Stripe customers and referral-earned plans (`plan_source = 'referral'`) never carry it. Full matrix: [BRANDING_MATRIX.md](BRANDING_MATRIX.md) |
| Unlimited ticket sales | `Event::hasTicketAllowance()` / `Role::canSellPaidTickets()` | No monthly cap on paid tickets. Selling itself is free (25/month); this removes the ceiling |
| Passes & subscriptions | `PassBookingService::isBookable()`; `EventRepo::saveEvent()` scrubs `is_pass` below Pro | Multi-use passes redeemable across events (visit pass, membership, festival pass, season pass); usage tracked on the Subscriptions tab; per-pass cancellation deadline and late-cancel policy (forfeit or block) |
| Individual tickets | `EventRepo::saveEvent()` scrubs `individual_tickets` below Pro. The RSVP variant is free | Collect per-attendee details; each guest gets own confirmation email and QR code |
| Unlimited appointment types | `Role::appointmentTypeLimit()` returns null above free | Offer consultations, lessons and rehearsals side by side |
| Generate event graphics | `GraphicController`, `$role->isPro()` | Auto-generated shareable images |
| REST API access | All `Api/*Controller.php`, `$role->isPro()` | Full CRUD API for events, schedules, sales, sub-schedules; read endpoints for post-event feedback and fan content |
| Webhooks | `WebhookService::dispatch()`, `$event->isPro()` | POST notifications for sales, events, check-ins |
| Event boosting with ads | `BoostController:101,202`, `$role->isPro()` | Meta Ads integration |
| Custom CSS styling | `RoleController:1748`, `$role->isPro()` | Custom CSS on schedule pages |
| Custom fields | `RoleController:1822`, `$role->isPro()` | Custom data fields on events. Each field can also be shown on the public event request form (on by default), so visitors answer it when submitting an event; text fields accept an optional validation pattern (ready-made presets or a regular expression) with a hint, enforced in the browser and on the server. Answers show on the Requests tab |
| Event polls | `EventController`, `$role->isPro()` | Create polls on events, guests vote |
| Event templates | `EventTemplateController`, `$role->isPro()` | Save an event as a reusable template and create new events from it (Templates tab) |
| Check-in dashboard | `CheckInController`, `$role->isPro()` | Real-time attendance tracking with per-ticket breakdown |
| Ticket waitlist | `WaitlistController::join()`, ticket branch only - the RSVP branch is free | Auto-notify when sold-out tickets become available |
| Sale notification emails | `EmailService::sendNewSaleNotification()` | Opt-in email alerts when tickets sell. The **first** paid sale on each event always notifies, on every tier |
| Push notifications | `OneSignalService::dispatch()`, `$role->isPro()` | Browser/mobile web push (via OneSignal) mirroring email notifications; opt-in, off by default, requires `ONESIGNAL_APP_ID` |
| Sales CSV export | `TicketController::exportSales()`, user-level `isPro()` (the export spans every schedule the user owns) | Export sales data with custom fields |
| Post-event feedback | `FeedbackController`, `$role->isPro()` | Collect star ratings and comments from attendees after events |
| Carpool matching | `CarpoolController`, `$role->isPro()` | Let attendees offer and request rides to events with driver approval, contact sharing, and reviews |
| Embed ticket widget | `RoleController::viewGuest()` for `?tickets=true` | Embed the ticket purchase form on external websites via iframe. The `?rsvp=true` embed is free |
| Promo/discount codes | `PromoCode::isValid()` (covers both the guest validate endpoint and the checkout apply step) plus an `EventRepo` persist scrub | Percentage or fixed discounts with usage limits and expiration dates |
| Gift cards | `GiftCardController`, `$role->giftCardsEnabled()` (`$role->isPro()`) | Sell balance-tracked gift cards buyers send to a recipient by email; redeemed toward tickets for any event on the schedule. Redemption of already-sold cards works even if selling is disabled |
| Installment payments | `EventRepo::saveEvent()` scrubs `installments_enabled` below Pro; `InstallmentService::ineligibleReason()` | Let buyers split a ticket over monthly payments. Configured per event on the Tickets > Payment tab (Stripe only). The first payment is taken at checkout and the ticket is valid immediately; the rest are charged off-session to the saved card by `app:charge-installments`. A plan that falls into arrears puts the ticket on hold at the door until it is settled. Progress, balances and a cash-flow forecast show on the Sales page > Installments tab |
| ~~Appointment booking~~ (moved to Free, capped at 1 type) | - | Calendly-style bookable appointment types (any duration, start-time interval, weekly hours, per-date overrides for holidays, buffers, optional payment via Stripe / payment URL / cash, optional approval); guests book a time on the public `/book` page. Rescheduling moves the existing booking (guest from their private link, owner from the Bookings row) rather than cancelling and rebooking, so the payment, private link and calendar entry carry over. Distinct from the Enterprise "Availability management" tab, which tracks whole-day team member availability |
| Eventbrite import | EventbriteController, $role->isPro() | Import events from Eventbrite |
| Bulk attendee import | `TicketController::importAttendees`, `$event->isPro()` | Import attendees in bulk from CSV or form entry (up to 5,000 rows per import) |
| Ticket add-ons | `EventRepo::saveEvent()` persist scrub | Sell extras alongside a ticket, with their own stock and per-order maximum |
| Invoice Ninja integration | `InvoiceNinjaController` | Alternative payment processing via Invoice Ninja |
| 100 newsletter emails per month | `$role->newsletterLimit()` | Increased email sending limit (counts each recipient as one email) |
| Unlimited fan photos + bulk download | `EventController`, `$role->isPro()` | No per-schedule photo cap; download all event photos as zip |
| Sponsor/partner logos | `RoleController`, `$role->isPro()` | Display sponsor logos with tiers on schedule page |
| Guest portal banner | `RoleController`, `$role->isPro()` | Show a custom announcement banner at the top of the schedule's guest pages |
| Custom guest favicon | `app-guest.blade.php` / `ticket/view.blade.php`, `$role->isPro()` | Schedule's logo becomes the browser-tab icon on its guest pages. The home-screen icon is NOT plan-gated: it follows the logo on every plan, and falls back to a neutral glyph rather than ours - see `AppController::scheduleManifest()` |

## Enterprise Features

Gated by `$role->isEnterprise()`.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Agenda scanning | `EventController::parseEventParts`, `$role->isEnterprise()` | Scan agendas to auto-create event parts |
| AI flyer generation | `EventController`, `$role->isEnterprise()` | Generate event flyer images from event details via OpenAI DALL-E |
| AI style generation | `RoleController`, `$role->isEnterprise()` | Generate cohesive schedule branding (profile/header/background images via OpenAI DALL-E, accent color and font via Gemini) |
| AI schedule details generation | `RoleController::generateScheduleDetails`, `$role->isEnterprise()` | Generate schedule short description and description via Gemini |
| AI event details generation | `EventController::generateEventDetails`, `$role->isEnterprise()` | Generate event category, short description, and description via Gemini |
| Save parsed event parts | `EventController:1654`, `$role->isEnterprise()` | Save AI-parsed event data |
| AI text processing on graphics | `GraphicController:298`, `$role->isEnterprise()` | AI prompt for graphic text via Gemini |
| Email scheduling (graphic emails) | `GraphicController:142`, `$role->isEnterprise()` | Schedule automated graphic emails |
| Allocated (reserved) seating | `SeatingPlanController`, `BoxOfficeController`, `EventRepo::saveEvent()`, `$role->isEnterprise()` | Reusable drag-and-drop seating plans (levels, sections, rows, tables, standing areas, wheelchair spaces), a guest seat picker, per-date edits, the box office console (hold back, phone booking, move, release) and the printable seating plan report. A plan already attached to an event survives the schedule lapsing; only attaching a NEW one is gated. Plans belong to VENUE schedules only - the Seating tab does not appear on a talent or curator schedule - but any schedule listing a seated event can still sell from the map and run the box office |
| Custom domains | `RoleController`, `$role->isEnterprise()` | Use your own domain for schedule |
| Internal & unlisted events | `EventRepo`, `$role->isEnterprise()` | Internal (members-only, never public) and Unlisted (hidden from the schedule but reachable by direct link, with an optional password) visibility options |
| Multiple team members | `RoleController::createMember/storeMember`, `$role->isEnterprise()` | Add/manage multiple team members. An **admin** runs the schedule day to day and sees its ticket sales, waitlist and check-in dashboard (`User::manageableRoles()` / `Event::scopeManagedBy()`); a **viewer** is read-only and sees no sales, but may scan tickets at the door (`User::canScanEvent()`). Only the owner changes levels or removes members (`RolePolicy::manageMemberLevels()`) |
| Availability management | `RoleController::availability`, `$role->isEnterprise()` | Team member availability tracking |
| 1,000 newsletter emails per month | `$role->newsletterLimit()` | Highest email sending limit (counts each recipient as one email) |
| WhatsApp event creation | `WhatsAppWebhookController`, `$role->isEnterprise()` | Create events via WhatsApp messages/images with AI parsing |
| Priority support | Not code-gated | Service-level commitment |

## Selfhost-Only Features

Available only when `IS_HOSTED=false` (selfhosted deployments).

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Auto import from URLs/cities | `resources/views/role/edit.blade.php:830`, `!config('app.hosted')` | AI-powered event import from external URLs and city search |
| App update | `app/Http/Controllers/AppController.php:19`, `!config('app.hosted')` | One-click application updates |

## Network Features

Available on any install that is **not** the nexus (`IS_NEXUS=false`) - that is, both
single-tenant selfhost and self-hosted SaaS. eventschedule.com itself is the receiving
end and has the moderation queue instead. Free on all tiers; enabled by the instance
operator, not per schedule.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Federation | `FederationService::isEnabled()` - `! config('app.is_nexus') && Setting::get('federation_enabled')` | Shares public events with the eventschedule.com listings; every listing links back to the event on the origin site. Off by default, enabled by an admin at `/admin/settings`. Each schedule can opt out via `roles.federation_enabled` |
| Federation moderation | `AdminFederationController`, `config('app.is_nexus')` | Nexus-only. Approve, suspend or delist instances, and block individual listings, at `/admin/federation` |

## Monetization (operator-enabled)

Off by default and **not a plan tier feature**: it exists only when the instance operator sets
`ADS_ENABLED=true` and configures it at `/admin/settings`. eventschedule.com does not enable it.
Multi-tenant hosted installs only - a single-tenant selfhost resolves to Enterprise, so it has no
free tier and is never monetized. See `/docs/saas/monetization`.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Ads on free schedules | `Role::showAds()` + `AdsService::isEligible()` / `resolveSlot()` | Google AdSense on free-tier public schedule and event pages. Never on paid tiers, embeds, checkout/booking/submission pages, **any event page that is actively selling tickets**, password-gated pages, custom domains, or for the schedule's own members. The selling exclusion exists because the free plan can now sell: an ad, or worse a paid promotion for a rival event, must not sit beside the organizer's own buy button. Non-personalized by default; honours `Sec-GPC` |
| Ad-free public pages | `Role::showAds()` returns false above free | The Pro-side benefit that mirrors "Remove Event Schedule branding" |
| Buy network promotions | `PromotionController`, `$role->isPro()` | Pro schedules buy placement for a public event on free schedules' pages (CPM or CPC, prepaid, unspent budget refunded). Stored as `boost_campaigns` rows with `channel = 'network'` |
| Host promotions opt-out | `roles.promotions_opt_out` | Free on all tiers: any schedule can decline to carry other schedules' promotions. Does not affect AdSense |
| Promotion review queue | `AdminController::approvePromotion/rejectPromotion` | Approve-before-serve, at `/admin/boost#promo-queue`. Rejection refunds in full. Auto-approves after `PROMOTIONS_AUTO_APPROVE_AFTER` clean campaigns |

## Accommodation Affiliate (operator-enabled)

Off by default and **not a plan tier feature**: it exists only when the instance operator sets
`STAY22_ENABLED=true`. Deliberately separate from Monetization above - it is independent of
`ADS_ENABLED`, it applies to **paid schedules as well as free ones**, and a schedule owner can
supply their own affiliate ID and keep the commission themselves. Works on single-tenant selfhosts
and on the nexus. See `/docs/saas/monetization#accommodation`.

The map loads nothing until the visitor has either accepted cookies or explicitly clicked to show
it, so no third-party request is made on page load. Suppressed for past events, embeds, `?graphic=1`,
password-gated pages, demo schedules, venues without coordinates, and - when the schedule has not set
its own affiliate ID - custom domains.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Nearby accommodation map | `roles.stay22_enabled` + `Stay22Service::embedFor()` | Free on all tiers. Per-schedule toggle in the Engagement section, off by default. Shows lodging near the venue on public event pages, with check-in/check-out derived from the occurrence |
| Keep your own commission | `roles.stay22_aid` | Free on all tiers, deliberately not Pro-gated. Blank means the commission goes to the instance operator, which the settings page discloses |
| Operator fallback ID | `Setting stay22_aid`, `/admin/settings` | Used for schedules that enabled the map without their own ID. Never used on a customer's custom domain |

## Paid Ticket Limits

Managed by `Role::ticketSaleLimit()` (`app/Models/Role.php`). Counts individual **paid** tickets, per
schedule, per calendar month. A ticket counts when the sale is `paid`, not deleted, its payment
method is neither `rsvp` nor `import`, and the ticket itself is not an add-on and has a price above
zero. Appointment bookings are excluded (they create real `Sale` and `SaleTicket` rows, so the
`events.appointment_type_id IS NULL` filter is mandatory). The window runs from the later of the
start of the month and `Role::freeSince()`, so a schedule that lapses mid-month is not judged on
what it sold while it was paying.

| Tier | Paid tickets per month |
|------|------------------------|
| Free | 25 (plus a per-owner backstop across all their schedules) |
| Pro | Unlimited |
| Enterprise | Unlimited |
| Selfhosted | Unlimited (`null`) |
| Demo schedule | Unlimited (`null`) |

Two rules keep the cap from landing at the worst possible moment:

- **Cash and other offline methods are counted but never blocked.** There is no processing cost to
  the operator, and refusing to record money taken at the door is indefensible.
- **An event starting within 48 hours is exempt** (`Event::TICKET_ALLOWANCE_GRACE_HOURS`). The
  allowance never stops sales for an event that is actually happening.

Zero-price tickets are always sellable, so an event mixing a free tier with paid ones keeps selling
its free tier at the cap (`Ticket::isSellable()`).

## Appointment Type Limits

Managed by `Role::appointmentTypeLimit()`. Free schedules get one fully-featured appointment type;
Pro, Enterprise, selfhosted and demo schedules are uncapped. Over-cap schedules (a lapsed Pro plan)
keep every type they created: `Role::bookableAppointmentTypes()` clamps the bookable set to the
oldest **bookable** type, and the rest return on upgrade.

## Newsletter Email Limits

Managed by `Role::newsletterLimit()` (`app/Models/Role.php`). Limits count individual email recipients, not newsletters. A newsletter sent to 100 followers uses 100 of the monthly allowance.

Automatic new-event announcements (`app:send-event-announcements`) are deliberately OUTSIDE this allowance. The promise is made to the guest at sign-up, not to the owner, so a free schedule that could not deliver it would be worse than not offering it at all. What bounds announcements instead is the cadence floor (`usage.audience_announcement_min_hours`, default 72) and `Role::canSendAudienceMail()`.

| Tier | Monthly email limit |
|------|---------------------|
| Free | 10 |
| Pro | 100 |
| Enterprise | 1,000 |
| Selfhosted (with own email settings) | Unlimited (`null`) |

## Key Code References

- **Plan tier detection**: `Role::actualPlanTier()` - `app/Models/Role.php`
- **Pro check**: `Role::isPro()` - returns `true` for Pro, Enterprise (an active Stripe subscription, a generic trial, or a legacy unexpired `plan_type`), and selfhosted. There is no testing or admin branch
- **Enterprise check**: `Role::isEnterprise()` - returns `true` for Enterprise and selfhosted. Same: no testing or admin branch
- **White-label check**: `Role::isWhiteLabeled()` - same logic as `isPro()`
- **Branding display**: `Role::showBranding()` - `false` on selfhost, otherwise `actualPlanTier() === 'free'`. NOT the inverse of `isWhiteLabeled()`: the selfhost case differs
- **Credit chip**: `Role::creditChipReason()` - `'selfhost' | 'saas' | 'granted_plan' | null`. Keyed on the deployment rather than the plan, then suppressed wherever `showBranding()` already puts the dark strip on the page, so no guest page carries both; see [BRANDING_MATRIX.md](BRANDING_MATRIX.md)
- **Newsletter limit**: `Role::newsletterLimit()` - returns limit based on tier
- **Paid ticket allowance**: `Role::ticketSaleLimit()`, `Role::ticketsSoldThisMonth()`,
  `Role::canSellPaidTickets()`, `Role::freeSince()` - `app/Models/Role.php`
- **Per-event selling gate**: `Event::canSellTickets()` / `Event::hasTicketAllowance()`; per-row
  `Ticket::isSellable()`
- **Appointment allowance**: `Role::appointmentTypeLimit()`, `Role::bookableAppointmentTypes()`
- **Payment timestamp**: `sales.paid_at`, stamped by the `Sale::saving()` hook. The allowance windows
  on this, never `created_at` - cash sales are created unpaid
- **Limit config**: `config/usage.php` (`ticket_sale_monthly_limit_free`,
  `ticket_sale_user_monthly_limit_free`, `appointment_type_limit_free`)
- **Event Pro check**: `Event::isPro()` - returns `true` if any associated schedule is Pro
- **Stripe config**: `config/services.php` lines 54-65
- **Plan management UI**: `resources/views/role/show-admin-plan.blade.php`
