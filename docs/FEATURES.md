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
| Claim a page created for you | Naming a performer or venue who is not on Event Schedule creates a schedule for them; they claim it by signing in with the address on it (`User::claimSchedule()`, `Role::isClaimable()`). Claiming carries the schedules already listing them into `approved_subdomains` so those dates keep appearing. "This is not me" takes the page down for whoever holds that address, and is recorded for review for anyone else |
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
| Email subscribers (audience capture) | `role_subscribers` + `App\Services\AudienceResolver`. A signed-out visitor gives the schedule an email address from the sign-up panel, the follow modal or a checkout tick box, and becomes a recipient. Confirming the sign-up panel or the follow modal also creates a passwordless account that follows the schedule, wherever `Role::willCreateAccountOnConfirm()` allows it (public registration open, schedule claimed, not a demo); a checkout opt-in never creates one. Double opt-in from the panel, single opt-in at checkout (the receipt already proves the address). Unlimited on every tier, and listed on the Followers tab. Works on selfhost, where the panel is the only capture surface: the Follow modal is hosted-only |
| Event interest capture ("tell me when tickets go on sale") | `EventInterestController` + `event_interests`. A signed-out visitor on a public event page leaves an **email address only** - no account, no name - and hears automatically when that event's tickets go on sale, if it is cancelled (the Cancel action notifies everyone there is to tell - `submitCancel()` in `event/edit.blade.php`), and once shortly before it starts; a date/time change (one-off events only) or a venue/online-link change reaches the list only when the organizer chooses to notify in the prompt on save (`EventRepo::saveEvent()`) (`app:send-event-interest-mail`, scheduled hourly on both rails). Per OCCURRENCE, so a recurring event's dates are separate, and the submitted date is checked with `matchesDate()` rather than taken on trust; an omitted one resolves through `Event::nextOccurrenceFrom()`, which is frequency-aware rather than reading `days_of_week` (that is what used to answer *today* for a monthly event). Refused at capture on a cancelled event, one hidden from discovery, or a past occurrence, and visibility is re-checked again at SEND time so an event moved to Draft or Unlisted after capture stops mailing strangers a link that would 404 for them. Offered in the Add to Calendar menu, and beside the buy button as a quiet link on an event that already sells. Deliberately **not** worded as a reminder: the control sits inside Add to Calendar, and somebody who has just added the event to their calendar already has one - what a calendar entry cannot do is say tickets went on sale, or that a downloaded `.ics` snapshot is now out of date. **Single opt-in**, stamped on create, matching the checkout rule rather than the subscribe panel's double opt-in: one affirmative act, about one named event, for a bounded set of messages, each carrying its own RFC 8058 one-click unsubscribe token. Unsubscribing DELETES the row rather than suppressing it - the relationship is bounded by one event, so there is nothing left to suppress, and a deletion is a real erasure. Rides the announcement mail rail (platform mailer, bounded by `Role::canSendAudienceMail()` with the REAL recipient count - passing a hardcoded 1 makes that gate inert, since it ends in `$recipients <= 50`), never the appointment rail, which skips any schedule without its own SMTP and would therefore be dark for most of the platform. The change and cancellation notices reach the list for the same reason: `NotifyEventChange` / `NotifyEventCancelled` no longer bail on `hasEmailSettings()`, because that gate belongs to the ticket-holder half and applying it in the job skipped both audiences. A row whose occurrence has passed leaves the send window in SQL rather than being stamped, so a rescheduled event is not silenced, and a dateless one ages out via `usage.event_interest_tickets_max_age_days`. Ungated, and it must stay that way: it exists to create the demand the free tier's 25 paid tickets a month are for. The count of people waiting shows on the event editor's Tickets panel and enriches the dashboard's existing "add a ticket type" step; it is never shown publicly. `tests/Feature/EventInterestTest.php`, `EventInterestSendTest.php` and `EventInterestBackupTest.php` fail the build if one of those properties is lost |
| Automatic new-event announcements | `app:send-event-announcements`. When a schedule publishes public events it created itself (`SendEventAnnouncements::newEventsFor()` filters `creator_role_id`, so an event another schedule lists on it, a curator's sourced events included, is never announced), its CONFIRMED subscribers get one digest covering the batch, floored at `usage.audience_announcement_min_hours` (default 72) per schedule. This is what the sign-up copy promises, so it is ungated and does NOT count against the newsletter allowance below - the cadence floor is what bounds it. Still subject to `Role::canSendAudienceMail()`, so an unverified schedule on the shared platform mailer reaches at most `usage.audience_mail_unverified_max_recipients` (default 50). Owner opt-out per schedule at Settings > Notifications, default on. Account followers are NOT included; they are reached only by a newsletter |
| Embed calendar on website | iframe embed with X-Frame-Options |
| Free event registration / RSVP | Native sign-up for free events with optional capacity limits. Unlimited on every tier, and never counted against the paid-ticket allowance below. The RSVP variants of the waitlist, per-guest individual registration and the `?rsvp=true` embed widget are free too - they always have been |
| Sell tickets (25 paid tickets per month) | `Role::ticketSaleLimit()`. Create ticket types and take payment, including online via Stripe Connect with **no platform fee**, the same as Pro. Capped at 25 paid tickets per calendar month per schedule, with a per-owner backstop across a user's schedules. Free RSVPs, zero-price tickets, add-ons and appointment bookings never count. Cash sales are counted but never blocked, and the cap never applies to an event starting within 48 hours. The first paid sale on each event always notifies the organizer |
| Ticket sales windows and volume discounts | Per-ticket-type sales start and end dates and group-rate (volume) discounts are saved with no plan scrub in `EventRepo::saveEvent()` (`volume_discount` is not in `$ticketExtrasAllowed`), so they work on every tier. Promo codes and add-ons are the Pro half |
| Ticket-type custom fields | Questions attached to a ticket type, asked once per order at checkout, have no plan gate in the event editor, `EventRepo` or checkout - unlike schedule and event custom fields and per-guest answers, which are Pro. Confirm this is intended: if it is gated later, `/features/custom-fields` (which now says Free) must change with it |
| Payment gateways (Stripe, PayPal, Payfast, Invoice Ninja, payment link, cash) | `config/payments.php` + `App\Services\Payments\PaymentGatewayManager`. Every gateway is available on every tier - there is NO `isPro()` check anywhere in `app/Services/Payments/`, and the connect route is gated on auth only. Connect one per account in Settings > Payment Methods and pick per event. A selfhost operator can instead supply one account for the WHOLE install in `.env`. For Stripe that is `STRIPE_PLATFORM_SECRET` and it is the only rail selfhost has (`User::canAcceptStripePayments()`) - there is no per-owner Stripe account on selfhost to override. For Payfast it is `PAYFAST_MERCHANT_ID`/`_KEY`/`_PASSPHRASE` (`PayfastGateway::platformCredentials()`), and there it is a default rather than an override: an owner who connected their own keeps using it. `DEFAULT_PAYMENT_METHOD` sets what a new event starts on instead of cash, honoured only where the gateway is actually usable. **Payfast** settles in South African rand only, so it is offered only on ZAR events, and a Payfast event cannot join the multi-event cart or use installments. For **PayPal** it is `PAYPAL_CLIENT_ID`/`_SECRET` (`PayPalGateway::platformCredentials()`), a default rather than an override on the same terms as Payfast, and `PAYPAL_WEBHOOK_ID` is optional because a payment is confirmed by re-reading the capture from PayPal rather than by the webhook signature. PayPal credentials are verified with PayPal before they are stored, so a mistyped secret is refused at the settings form rather than by a buyer (Invoice Ninja does the same, at `ProfileController`). It settles PayPal's own currency list minus HUF, JPY and TWD, which are excluded deliberately: PayPal rejects a decimal amount in all three, and the pricing path produces one whatever the currency - `PromoCode::calculateDiscount()` rounds against `Event->currency_code`, an attribute that does not exist (the column is `ticket_currency_code`), so `decimalsFor()` always sees null and returns 2. With `SaleSettlementService::AMOUNT_TOLERANCE` a flat 0.01, every discounted sale in those three would land in `amount_mismatch`: money captured, ticket withheld. That rounding bug is pre-existing and platform-wide - it mis-rounds for Stripe too, where it is invisible because Stripe accepts the decimals - so this exclusion is containment, not a fix. A PayPal event CAN join the multi-event cart (one order, one capture) but cannot use installments. Invoice Ninja is included on the same terms: it too has no `isPro()` check, in `InvoiceNinjaGateway`, `InvoiceNinjaController` or the `ProfileController` settings write |
| Refunds, full or partial | `SaleRefundService` + `PaymentGatewayDriver::supportsRefunds()`. Refunding a **Stripe** or **PayPal** sale sends the money back through the provider and only then changes the status. For Stripe that is on the rail the sale resolves to now (Connect account on hosted, platform keys on selfhost); an owner who has since reconnected a different Stripe account will get a failure rather than a refund from the wrong account, because the rail is re-derived rather than snapshotted; only installment plans record the account they charged. A partial refund leaves the sale `paid` with its tickets valid, and the sales page shows how much has gone back so far. An installment plan is refunded leg by leg, in full only. For PayPal the capture id in `transaction_reference` is what a refund is issued against, and because that id has no distinguishing prefix a reference that is not capture-shaped falls back to Mark as Refunded. Every other rail - Invoice Ninja, Payfast, payment link, cash, or a sale marked paid by hand - shows **Mark as Refunded**, which records the refund without moving money. Free on every tier, like the rest of ticketing. An unconfirmed refund is parked for a person rather than retried, because a retry after the idempotency key expires is how one refund becomes two. Which failures count as unconfirmed is the driver's call (`PaymentGatewayDriver::classifyRefundFailure()`): the service's own ladder recognises only Stripe's exception classes, so a driver on a different HTTP client must classify its own or have every refusal parked forever |
| Invoice Ninja integration | `InvoiceNinjaController` + `InvoiceNinjaGateway`. Bill a ticket sale as an invoice or a payment link in your own Invoice Ninja company, with the QR code written into the invoice notes and a webhook marking the sale paid. Free like every other gateway: grep for `isPro` under `app/Services/Payments/` and the answer is still nothing |
| Appointment booking (1 appointment type) | `Role::appointmentTypeLimit()`. Fully featured otherwise: weekly hours, per-date overrides, buffers, approvals, and payment via Stripe / payment URL / cash. A schedule that lapses from Pro keeps every type it created; only the oldest bookable one stays bookable, and the rest return on upgrade |
| QR code scanning at the door | `TicketController::scan()` / `scanned()`, gated only by `User::canScanEvent()` (a permission check for owners, admins and viewers - it has NO plan check). The "Scan Ticket" button on the Sales tab renders unconditionally. Scan the QR on any ticket or registration, including the 25 paid tickets a month the free plan sells; each ticket admits once and a re-scan warns. The live **Check-in dashboard** (running count and per-ticket-type breakdown) is the Pro half - see below |
| Add to Google Wallet | `App\Services\Wallet\GoogleWalletService`, no `isPro()` check anywhere - the wallet pass is a nicer container for a QR every tier already gets, so it is gated on nothing but configuration. An "Add to Google Wallet" button appears on the buyer's ticket page, on the multi-event order page and in the confirmation email; the pass carries the same `ticket.view` URL as the on-page QR, so it scans through the free door scanner unchanged. Opt-in per INSTALL, not per schedule: it needs a Google Wallet issuer account in `GOOGLE_WALLET_ISSUER_ID` / `GOOGLE_WALLET_SERVICE_ACCOUNT` (see `docs/GOOGLE_WALLET_SETUP.md`), and with those unset no button renders and nothing is sent to Google. The pass is a snapshot and is never updated after it is saved, but a cancelled or refunded order is still refused at the door by `TicketController::scanned()` |
| Subscribe to a schedule's calendar | `FeedController::icalFeed`, `/{subdomain}/feed/ical` (plus `/feed/rss`). A live feed the visitor's calendar re-reads, so a moved date updates itself - unlike the one-off `.ics` download below, which is a snapshot. Offered as the last row of the Add to Calendar menu on an event page that is not leading with Buy or Register (`event/show-guest.blade.php`, header menu and mobile sheet; the two ticketed-event dropdowns do not carry it), and in the sign-up panel for signed-out visitors on schedule and event pages (`partials/subscribe-panel.blade.php`). The route has always existed and was public and unauthenticated; until now it was surfaced only in a signed-in user's following list and the owner's own settings, so no guest could find it. Costs no email address and no consent, which is why it is the part of the interest feature that cannot fail on deliverability |
| iCal download | Download .ics files for individual events and recurring event dates |
| Fan photos on events (25 per schedule) | User-submitted photos with approval workflow; upgrade prompt at limit |
| Event cloning | Duplicate an existing event as a starting point for a new one |
| Generate event graphics | `GraphicController` has had no plan check since `0540a6b39` (2026-01-25, "Remove Pro plan requirement for events graphics page access"): any member can generate and download a schedule graphic and its text on every plan, and the dashboard link renders for everyone. On the hosted service every graphic carries an eventschedule.com credit (`AbstractEventDesign::renderBranding()`, keyed on hosting, not plan). The AI text prompt and scheduled graphic emails are Enterprise (rows below) |
| Venue logo wall header | Banner header option showing logos of venues (talents for venue schedules) from approved public events; drag-reorderable on the edit page |
| Backup & restore | Export and import schedule data with optional images |
| 10 newsletter emails per month | Basic newsletter email sending limit (counts each recipient as one email). Automatic new-event announcements are separate and do not draw on it |
| AI event parsing | `EventController::parse`, capped by `Role::aiParseDailyLimit()`: 10 a day while a schedule is on its trial, 50 a day otherwise (free and Pro alike - there is no free-tier branch), 100 a day on Enterprise, unlimited selfhosted. Parses event details from text and images via Gemini |

## Pro Features

Gated by `$role->isPro()`. Enterprise users also get all Pro features.

| Feature | Gate location | Notes |
|---------|--------------|-------|
| Remove Event Schedule branding | `$role->isWhiteLabeled()` / `$role->showBranding()` | White-label, removes "Powered by" from all seven surfaces. The corner credit chip is decided separately by `Role::creditChipReason()`: on any install that is not eventschedule.com - a plain selfhost or an operator's own platform - it is the AAL attribution owed by whoever redistributes the software, so no plan buys it off. It stands down wherever the dark strip already renders, so on an operator's platform it lands on the tiers they charge for and their free tier shows the strip alone. On the nexus the one case is an Enterprise plan an admin granted by hand (`roles.plan_source = 'admin'`, no active Stripe subscription); Stripe customers and referral-earned plans (`plan_source = 'referral'`) never carry it. Full matrix: [BRANDING_MATRIX.md](BRANDING_MATRIX.md) |
| Unlimited ticket sales | `Event::hasTicketAllowance()` / `Role::canSellPaidTickets()` | No monthly cap on paid tickets. Selling itself is free (25/month); this removes the ceiling |
| Passes & subscriptions | `PassBookingService::isBookable()`; `EventRepo::saveEvent()` scrubs `is_pass` below Pro | Multi-use passes redeemable across events (visit pass, membership, festival pass, season pass); usage tracked on the Subscriptions tab; per-pass cancellation deadline and late-cancel policy (forfeit or block) |
| Individual tickets | `EventRepo::saveEvent()` scrubs `individual_tickets` below Pro. The RSVP variant is free | Collect per-attendee details; each guest gets own confirmation email and QR code |
| Unlimited appointment types | `Role::appointmentTypeLimit()` returns null above free | Offer consultations, lessons and rehearsals side by side |
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
| Installment payments | `EventRepo::saveEvent()` scrubs `installments_enabled` below Pro; `InstallmentService::ineligibleReason()` | Let buyers split a ticket over monthly payments. Configured per event on the Tickets > Payment tab (Stripe only). The first payment is taken at checkout and the ticket is valid immediately; the rest are charged off-session to the saved card by `app:charge-installments`. A declined payment is retried (`app:charge-installments`). While a plan is in arrears, scanning its ticket shows an amber hold with the buyer's name and balance instead of admitting it (no check-in is recorded, so letting them in is the door's call); paying the balance or saving a new card lifts the hold (`InstallmentService`). Progress, balances and a cash-flow forecast show on the Sales page > Installments tab |
| ~~Appointment booking~~ (moved to Free, capped at 1 type) | - | Calendly-style bookable appointment types (any duration, start-time interval, weekly hours, per-date overrides for holidays, buffers, optional payment via Stripe / payment URL / cash, optional approval); guests book a time on the public `/book` page. Rescheduling moves the existing booking (guest from their private link, owner from the Bookings row) rather than cancelling and rebooking, so the payment, private link and calendar entry carry over. Distinct from the Enterprise "Availability management" tab, which tracks whole-day team member availability |
| Eventbrite import | EventbriteController, $role->isPro() | Import events from Eventbrite |
| Bulk attendee import | `TicketController::importAttendees`, `$event->isPro()` | Import attendees in bulk from CSV or form entry (up to 5,000 rows per import) |
| Ticket add-ons | `EventRepo::saveEvent()` persist scrub | Sell extras alongside a ticket, with their own stock and per-order maximum |
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
| Auto import from URLs | `resources/views/role/edit.blade.php` (the `section-auto-import` panel), `!config('app.hosted')` | AI-powered daily import (`app:import-curator-events`, `routes/console.php`) of events from a list of external URLs. The optional city list is a filter, not a search: `ImportCuratorEvents` drops an imported event whose city is not on it |
| App update | `can_self_update()` (`app/helpers.php`): any signed-in user on a plain selfhost, admins only on a self-hosted SaaS install, never on eventschedule.com | One-click application updates |

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
