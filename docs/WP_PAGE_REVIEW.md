# WP Page Review Tracker

A checklist of every WP (marketing) site page, used to track review progress as each page is reviewed.

**Legend:** a checkmark in the **Reviewed** column marks a page as reviewed; a blank cell means it has not been reviewed yet. The **Updated** column marks pages that have a page-exclusive, ground-up design - either motif-driven (a large page-local `<style>` block with its own class namespace, headed by a `/* ... "Nickname" ... */` concept comment) or component-driven (restructured onto the shared components in `resources/views/components/marketing/`). A blank cell means the page is still on the first-wave es-* skeleton with a themed `.text-gradient-<page>` accent and is a candidate for a rebuild; the Notes cell of an updated page names its design. **The docs tables are the deliberate exception:** the 37 documentation pages share ONE restrained shell on purpose so the manual reads as a single book, so ✅ there records the July 2026 *accuracy and legibility* pass (every capability claim re-verified against code, stale UI labels fixed, contrast measured) rather than a page-exclusive design. On the audience "For" pages table, Updated also means that page's restyle brief (the Notes cell) has been applied.

**Progress:** 153 / 153 reviewed

**Updated:** 47 / 153 rebuilt

> **Ticketing plan model change - SWEPT 2026-07-31.** Another session shipped a real product
> change mid-campaign: the FREE tier now SELLS paid tickets, 25 per calendar month per
> schedule (`Role::ticketSaleLimit()`, `config('usage.ticket_sale_monthly_limit_free')`) with
> a 50/month per-owner backstop. Selfhost, demo schedules and Pro/trialling are unlimited;
> non-addon zero-price tickets and events starting within 48 hours are exempt
> (`Event::hasTicketAllowance()`). **Pro now means UNLIMITED ticket sales, not "ticketing".**
> Still Pro: QR check-in dashboard, individual tickets, passes, waitlist, promo codes,
> add-ons, sales CSV export, gift cards, bulk import, the ticket-purchase embed.
>
> A sentence-level scan found the stale claim on **21 pages** in three different phrasings
> ("is on Pro", "are five dollars a month", "is $5 a month") - token greps had under-counted
> it twice. All 21 were corrected one page at a time, each rewritten in its own page's voice
> rather than by find/replace, which mangled prose here on a previous occasion. `/pricing` was
> already correct and served as the reference; `contact`, `docs/subscriptions`,
> `docs/selfhost/index` and `docs/analytics` were verified correct and left alone.
>
> Two further plan facts surfaced during the sweep and were fixed in passing: **appointment
> booking is now FREE** (`Role::appointmentTypeLimit()`, one type free) and **volume discounts
> were never Pro** (`volume_discount` is not in EventRepo's `$ticketExtrasAllowed` scrub).
> `for-musicians` also carried the long-standing "newsletters are available on the Pro and
> Enterprise plans" error - newsletters are FREE at 10 recipients/month - now corrected.
>
> **Door scanning is FREE - resolved and swept 2026-07-31.** The product owner decided free
> users may scan the 25 tickets a month the free plan sells. **No code change was needed:**
> `TicketController::scan()` and `scanned()` carry no plan gate at all (`scan()` even has a
> comment saying it is available on every plan because the Pro feature is the dashboard),
> `User::canScanEvent()` is a permission check for owners/admins/viewers with no `isPro()`,
> and the "Scan Ticket" link in `ticket/sales.blade.php` renders outside the `@if` that guards
> the dashboard link. The live **check-in dashboard** (`CheckInController::index/stats`,
> running count and per-ticket-type breakdown) stays Pro, which is exactly what `/pricing`
> already said.
>
> `docs/FEATURES.md` was the source of the error: it listed "QR code check-ins" under **Pro**
> and claimed "the scan chokepoint is `User::canScanEvent()`" - a function with no plan check.
> That row is now a **Free** row, "QR code scanning at the door", citing the real code path;
> the separate "Check-in dashboard" Pro row is untouched. Then 20 marketing and docs pages
> were corrected one at a time. Several kept a half that is still true - passes, promo codes,
> add-ons, waitlist, custom fields and the live count all remain Pro - so this was never a
> blanket replace. The sharpest result is on `/for-libraries`: "Scanning the QR on a ticket is
> free on every plan; it is the running total that is Pro."

> **Feature-claim accuracy audit (2026-07-31): 305 contradictions found and fixed.** The first
> site-wide check of WP copy against what the app actually does, prompted by the fact that
> accuracy had only ever been checked per page, at different times, against a `FEATURES.md`
> that changed twice mid-campaign. A polarity-aware checker (`audit-claims.py`) ran over all
> 151 pages - it has to be polarity-aware because these pages are full of deliberate honesty
> statements ("there is no seat map") that a naive grep reports as defects. Then 25 high-risk
> pages got a per-page agent required to cite the model, column or code path behind every
> capability claim, or delete it.
>
> The worst finding was on `/for-circus-acrobatics`, a page that had already been rebuilt
> *with* a documented fabrication sweep: a mock streaming player with a LIVE badge,
> "847 watching", a live chat feed of invented viewer comments, and "$25 tip from Sarah!".
> **No tipping code, no chat model and no viewer counter exist anywhere.** Also removed: the
> homepage newsletter mock showing a send of 1,248 recipients (impossible - the Enterprise
> ceiling is 1,000), "Followers get notified when you add events" on `/use-cases` (there is no
> automatic follower notification at all), and a claim that one extra team member is free
> (`RoleController::createMember()` hard-returns unless `isEnterprise()`).
>
> Result: **0 nonexistent-feature claims and 0 impossible numbers site-wide.** Two categories
> are left deliberately unfixed because they are risks rather than present contradictions:
> 5 pages hand-roll `FAQPage` JSON-LD instead of `<x-seo.faq-schema>` (verified on the
> rendered HTML - every JSON-LD question does appear visibly today, so nothing is drifting),
> and 57 pages hardcode "$5"/"$15" while `/pricing` reads them from
> `config('services.stripe_platform.*')` (all correct today; they only go stale if the price
> changes).

> Scope: static and functional marketing pages served under `marketing.*` routes (`routes/web.php`, `MarketingController`), cross-checked against `resources/views/sitemap.blade.php`. Excludes URL redirects, the shared partials/components, and individual blog posts. The comparison and replacement detail pages each render one shared template driven by per-slug data.

## Main / Top-level (14)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Homepage | `/` | Homepage-exclusive design (poster wall, 3D showcase, pinned gallery rail, integrations orbit). 2026-09 pass: reserved seating + gift cards added to the grid, calendar/analytics/AI/ticketing claims re-grounded, FAQ expanded to 7 with payouts and custom domains, how-it-works corrected for automatic new-event digests, claim input no longer a dead end. |
| ✅ | ✅ | Features | `/features` | Five chapters via `<x-marketing.feature-chapter>` / `<x-marketing.feature-banner>`. 2026-09 pass: new "small print" section covering 16 real capabilities the page never named (installments, add-ons, waitlist, bulk import, WhatsApp, agenda scanning) with per-row tier badges; private-events banner rebuilt around the four real visibility states; newsletters, fan content and analytics claims re-grounded. |
| ✅ | ✅ | Pricing | `/pricing` | Rebuilt July 2026 (the feature lists are curated - see CLAUDE.md before editing them). 2026-09 pass: added the two questions asked most before signing up - what happens at the 25-ticket cap, and what happens on cancel or downgrade - answered from `Role::ticketSaleLimit()` / `paidTicketAllowanceAvailable()` behaviour; fee answer rewritten around direct payouts; fixed a hardcoded `$` beside our own Pro price in the fee calculator. |
| ✅ | ✅ | About | `/about` | "The Colophon" ground-up rebuild. 2026-09 pass: errata corrected now that `app:send-event-announcements` ships - the automatic digest reaches confirmed subscribers, while account followers stay newsletter-only, and the double-booking erratum now excludes appointments, which genuinely cannot clash. |
| ✅ | ✅ | Demos / Examples | `/examples` | "The Showroom" ground-up rebuild; route name is `marketing.demos`. 2026-09 pass: all 18 demo schedules re-verified live (HTTP 200), and the spec sheet now says the Follow button earns an automatic new-event digest, not just a list to write to. |
| ✅ | ✅ | Search | `/search` | "The Lookup" ground-up rebuild; functional page, search form and query handling preserved. 2026-09 pass: the "when you sell a ticket" row was badged Pro while the page FAQ said Free two sections later - selling is free to 25 paid tickets a month, so the badge and copy now agree; follower row corrected for automatic digests. |
| ✅ | ✅ | Browse | `/browse` | "The Newsstand" ground-up rebuild; functional page, filters and query params preserved. 2026-09 pass: added the question organizers actually ask - how an event ends up on the page - answered from `MarketingController::browse()` (public + a flyer, or a talent/venue profile photo, 24 shown). |
| ✅ | ✅ | FAQ | `/faq` | "The Front Desk" ground-up rebuild. 2026-09 pass: rate card split QR scanning (free on every plan) out of the Pro check-in dashboard row, and three answers that still called ticketing a paid feature were corrected against `Role::ticketSaleLimit()`; 05.03 rewritten now that `app:send-event-announcements` reaches confirmed subscribers. |
| ✅ | ✅ | Why Create an Account | `/why-create-account` | "The Keyring" ground-up rebuild. 2026-09 pass: two more genuinely open doors added (hearing about new events via the guest sign-up panel, and submitting an event), keeping the grid at 2x3, and the ledger`s Follow row now separates the Follow button, which needs an account, from leaving an email address, which does not. |
| ✅ | ✅ | Use Cases | `/use-cases` | Component-driven directory via `<x-marketing.audience-card>`. 2026-09 pass: all 31 audience links verified against the route table; the curator "Build Your Audience" tile now states the automatic new-event digest, with the subscriber-vs-follower distinction pinned in a comment so it cannot drift back. |
| ✅ | ✅ | Contact | `/contact` | "The Postcard" ground-up rebuild. 2026-09 pass: added a private security-disclosure row to the routing table (the page previously sent "something is broken" straight to a public issue tracker with no stated alternative), and the two-of-five self-service note now reads six. |
| ✅ | ✅ | Open Source | `/open-source` | "The Commit Log" ground-up rebuild. 2026-09 pass: the hosted-vs-selfhost diff billed QR check-in as Pro when scanning has no plan check at all - the row is now the check-in dashboard. API surface (24 authenticated endpoints), OpenAPI counts (16 paths, 26 operations) and the four machine-readable files re-verified against `routes/api.php` and `public/api/openapi.json`. |
| ✅ | ✅ | Selfhost | `/selfhost` | "The Terminal" ground-up rebuild. 2026-09 pass: the "yours to run" list gained the two responsibilities selfhosters discover late - outbound SMTP, which the setup wizard deliberately does not write, and disk for uploaded flyers, photos and generated graphics - and the grid moved to 3-wide so six cells fill cleanly. |
| ✅ | ✅ | SaaS | `/saas` | "The Stack" ground-up rebuild; white-label SaaS operator landing. 2026-09 pass: added the three operator revenue rails the page never mentioned (AdSense on your free tier, prepaid promotions between your tenants, the Stay22 accommodation affiliate) with their real gates and exclusions, linked to `/docs/saas/monetization`; timeline pills no longer wrap in the third comparison card. |

## Feature pages (33)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Ticketing | `/features/ticketing` | "The Turnstile" ground-up rebuild. 2026-09 pass: added installments and the multi-event cart to the sale side (both real checkout behaviour the page never named), and the sale-notification card now states the first paid sale on an event always notifies, on any plan. |
| ✅ | ✅ | Gift Cards | `/features/gift-cards` | "The Gift Envelope" ground-up rebuild. 2026-09 pass: every number re-verified in code (max:12 denominations, max:500 message, redeem-after-promo ordering) and the checkout section gained the one behaviour it was missing - a card pays for a whole multi-event cart in one go, not event by event. |
| ✅ | ✅ | Allocated Seating | `/features/allocated-seating` | Reserved-seating landing page (was missing from this tracker entirely). 2026-09 pass: the 12-minute hold and the orphan-seat rule re-verified against `SeatHoldService::HOLD_SECONDS` and `OrphanSeatRule`; two FAQs added for the two things that surprise people - plans are built on VENUE schedules only, and a plan already attached to an event survives the schedule lapsing. |
| ✅ | ✅ | AI | `/features/ai` | "The Spark" ground-up rebuild. 2026-09 pass: field list, venue-matching ladder, allowances and the WhatsApp path all re-verified in code and already exact; added the one path the page never covered - `EventController::guestParse` gives visitors filling your public submission form the same parser, metered against your allowance rather than theirs. |
| ✅ | ✅ | Calendar Sync | `/features/calendar-sync` | "The Round Trip" ground-up rebuild. 2026-09 pass: reviewed against `GoogleCalendarService`, the Graph sync and the CalDAV poller - both legs, the fifteen-minute sweep, the delete-sync choice and the loop guard are all stated correctly; no changes needed. |
| ✅ | ✅ | Analytics | `/features/analytics` | "The Dashboard" ground-up rebuild. 2026-09 pass: the intake gates, the nine dials, the one-row-a-day record and the revenue/check-in tabs all re-verified and already exact (this page had the QR-scanning tier right when three others had it wrong); the "want names" line now states the automatic subscriber digest and that it sits outside the newsletter allowance. |
| ✅ | ✅ | Integrations | `/features/integrations` | "The Wire" ground-up rebuild. 2026-09 pass: Payfast was a shipped gateway missing from a page that claims to list every port in full, so the panel and register are now thirteen ports with its ZAR-only constraint, ITN callback and cart/installment exclusions written down; the fourteen webhook types re-verified against `Webhook::EVENT_TYPES`. |
| ✅ | ✅ | Custom Fields | `/features/custom-fields` | "The Form" ground-up rebuild. 2026-09 pass: the ten-field cap, the six types, the four switches and the five validation presets all re-verified against `RoleUpdateRequest` and the edit form; the free/Pro line on this page was already correct about scanning and the 25-ticket allowance. |
| ✅ | ✅ | Custom Labels | `/features/custom-labels` | "The Rename" ground-up rebuild. 2026-09 pass: reviewed, accurate as written - the override set is per schedule rather than per account, and the page is careful that renaming a word moves nothing else on the page. |
| ✅ | ✅ | Team Scheduling | `/features/team-scheduling` | "The Lineup" ground-up rebuild. 2026-09 pass: the owner card claimed the position "cannot be handed over from the Team tab" when Transfer ownership is a button on that exact tab, deliberately un-gated by plan - corrected, since it is the one multi-person action a free schedule has. |
| ✅ | ✅ | Sub-schedules | `/features/sub-schedules` | "The Sort" ground-up rebuild. 2026-09 pass: reviewed, accurate as written - the four-field record matches the `Group` model exactly, and the page is careful that a sub-schedule sorts rather than hides. |
| ✅ | ✅ | Online Events | `/features/online-events` | "Go Live" ground-up rebuild. 2026-09 pass: reviewed and accurate throughout, including the three attendance modes in the structured data, the domain-on-the-listing / full-link-on-the-ticket split, the Teams exception, and the schedule-timezone rule with its honest caveat; no changes needed. |
| ✅ | ✅ | Newsletters | `/features/newsletters` | "The Send" ground-up rebuild. 2026-09 pass: the page was built on "nothing sends itself", which `app:send-event-announcements` made false. Section 01 is now the two real rails - an automatic, batched, three-day-floored digest to confirmed subscribers that spends no envelopes, beside the newsletter you write - with the hero, dot nav and the style block`s concept note rewritten to match and `.es-send-void` removed as dead. |
| ✅ | ✅ | Recurring Events | `/features/recurring-events` | "The Loop" ground-up rebuild. 2026-09 pass: the six patterns, three end conditions and both exception directions re-verified against `Event::matchesDate()`; the page title and JSON-LD said "automatic Google Calendar sync" when Outlook and CalDAV sync the same way, now corrected. |
| ✅ | ✅ | Embed Calendar | `/features/embed-calendar` | "The Paste" ground-up rebuild. 2026-09 pass: reviewed and accurate throughout, including all six URL parameters, the noindex on the embed URL, the deliberate exclusion of embed loads from view counts, and the live iframe demo on the page itself; no changes needed. |
| ✅ | ✅ | Embed Tickets | `/features/embed-tickets` | "The Widget" ground-up rebuild. 2026-09 pass: reviewed and accurate - the seven layers are the real checkout, the sold-out state becomes the waitlist form, and the free RSVP variant is correctly separated from the Pro ticket widget; no changes needed. |
| ✅ | ✅ | Fan Videos | `/features/fan-videos` | "The Reel" ground-up rebuild. 2026-09 pass: reviewed and accurate - photos, YouTube links and comments, the reject-deletes-rather-than-hides rule, the per-occurrence filing on a recurring show, and the free-tier 25-photo cap all check out; no changes needed. |
| ✅ | ✅ | Polls | `/features/polls` | "The Vote" ground-up rebuild. 2026-09 pass: reviewed against `EventPoll` - the five-per-event and two-to-ten limits, the seal-on-first-vote rule, and both write-in modes including the pending queue are all stated correctly; no changes needed. |
| ✅ | ✅ | Boost | `/features/boost` | "The Launch" ground-up rebuild. 2026-09 pass: reviewed and accurate - five pre-launch gates, both channels (Meta and the on-network promotion), prepay-and-refund, and the audience section already states the automatic subscriber digest correctly; no changes needed. |
| ✅ | ✅ | Private Events | `/features/private-events` | "The Vault" ground-up rebuild. 2026-09 pass: reviewed and accurate - the four states match `Event::visibilityState()` exactly, with the password correctly attached to Unlisted and both Enterprise states badged; no changes needed. |
| ✅ | ✅ | Event Graphics | `/features/event-graphics` | "The Gallery" ground-up rebuild. 2026-09 pass: reviewed and accurate - the generator hangs flyers already on the events rather than pretending to be a design tool, and the template-variable list matches `EventTextGenerator::parseTemplate()`; no changes needed. |
| ✅ | ✅ | White Label | `/features/white-label` | "The Blank Slate" ground-up rebuild. 2026-09 pass: reviewed against `Role::showBranding()` / `creditChipReason()` and BRANDING_MATRIX.md - all seven surfaces, the plan-tier keying and the credit chip that no plan buys off are stated exactly; no changes needed. |
| ✅ | ✅ | Custom CSS | `/features/custom-css` | "The Stylesheet" ground-up rebuild. 2026-09 pass: reviewed and accurate - the cascade position (same sheet, immediately after the generated styles, equal specificity) is the honest description of what the feature is; no changes needed. |
| ✅ | ✅ | Custom Domain | `/features/custom-domain` | "The Nameplate" ground-up rebuild. 2026-09 pass: reviewed and accurate - both modes, automatic HTTPS once the CNAME resolves, and the rewriting of links, feeds and the checkout return URL all check out against `ResolveCustomDomain` and the provisioning contract; no changes needed. |
| ✅ | ✅ | Feedback | `/features/feedback` | "The Comment Card" ground-up rebuild. 2026-09 pass: reviewed and accurate - one card per booking, the 1-to-48-hour window, the 30-day cutoff, the 2,000-character comment cap and the `feedback.submitted` webhook all check out; no changes needed. |
| ✅ | ✅ | Availability | `/features/availability` | "Office Hours" ground-up rebuild. 2026-09 pass: reviewed and accurate - the cross-out-what-is-gone model, the Enterprise talent-schedule scope, and its separation from bookable appointments are all correct; no changes needed. |
| ✅ | ✅ | Appointments | `/features/appointments` | "The Appointment Book" ground-up rebuild. 2026-09 pass: the page still called booking a Pro feature in four places. `Role::appointmentTypeLimit()` gives a free hosted schedule one fully-featured type and Pro uncaps the count, so the FAQ, the plan note, the availability comparison and the finale line now say that, including what a lapse actually does (every type kept, the oldest bookable one stays bookable). |
| ✅ | ✅ | Carpool | `/features/carpool` | "Four Seats" ground-up rebuild. 2026-09 pass: reviewed and accurate - the six-field offer record, driver approval before contact details are shared, and the deliberate absence of routing or payment are all stated plainly; no changes needed. |
| ✅ | ✅ | Google Calendar | `/google-calendar` | "The Invitation" ground-up rebuild; root-level integration page. 2026-09 pass: reviewed and accurate - OAuth connect, per-schedule calendar choice, the watch channel plus fifteen-minute sweep, and the location-to-venue conversion all check out; its Google-only framing is correct for a page about Google. |
| ✅ | ✅ | Outlook Calendar | `/outlook-calendar` | "The Meeting Request" ground-up rebuild; root-level integration page. 2026-09 pass: reviewed and accurate - Graph change subscriptions, delta tokens, the optional Teams meeting written back into the event link field, and the schedule-timezone stamping all check out; no changes needed. |
| ✅ | ✅ | CalDAV | `/caldav` | "The Open Protocol" ground-up rebuild; root-level integration page. 2026-09 pass: reviewed and accurate - RFC 4791 over HTTPS only, encrypted credentials, the six requests, and the honest fifteen-minute poll because CalDAV has no notification standard; no changes needed. |
| ✅ | ✅ | Stripe | `/stripe` | "The Payout" ground-up rebuild; root-level integration page. 2026-09 pass: reviewed and accurate - the charge is created on the connected account with no application fee, and the page argues zero fees as a statement line rather than a promise; no changes needed. |
| ✅ | ✅ | Invoice Ninja | `/invoiceninja` | "The Ledger" ground-up rebuild; root-level integration page. 2026-09 pass: verified the tier this page implies - Invoice Ninja has NO `isPro()` gate anywhere (gateway, controller, or the settings write), so `docs/FEATURES.md` was corrected to move it out of the Pro table, and `/about` no longer calls it a Pro-only payment route. |

## Schedule Type Hubs (3)

> The three top-level audience pages, one per schedule type (Talent, Venue, Curator). Each lives at `resources/views/marketing/for-{slug}.blade.php`.

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | For Talent | `/for-talent` | Rebuilt July 2026 to match `/`, `/features`, `/pricing` and `/use-cases`: brand blue→sky→cyan (retiring the amber/rose "Center Stage" gels), hero schedule mockup, a five-banner "Life of a gig" run via `<x-marketing.feature-banner>`, a `#live` rail of real talent events (hides below 4), the `#keep` fee band, an accurate free/Pro split, and the 12 performer cards via `<x-marketing.audience-card>` off the shared `config/marketing_audiences.php`. |
| ✅ |   | For Venues | `/for-venues` | "Front of House" ground-up rebuild, July 2026. Two acts: front of house (calendar, box office, own brand, private hire) and back of house (booking inbox, the door, rooms, the numbers), hinged by a full-bleed "pass door" that carries the mid-page CTA. The two acts read through copy, ground colour and lighting - the facade-elevation and floor-plan line drawings that originally carried the motif were removed in the July 2026 line-drawing sweep. Act 02 uses the new `ground="dark"` on `<x-marketing.feature-banner>`/`feature-chapter` (fixed dark in both colour modes). Adds a week board, a "stack this replaces" row linking seven `replace`/`compare` pages, a venue-specific plan strip reading prices from config, `HowTo` schema, and the 12 venue cards via `<x-marketing.audience-card>` off the shared `config/marketing_audiences.php`. Fixed a wrong FAQ answer that claimed a "door staff" role. |
| ✅ |   | For Curators | `/for-curators` | "The Listings" ground-up rebuild |

## Audience "For" pages (31)

> **Restyle briefs.** Each note below can be pasted into Claude as the instruction for making that page's design more unique to its audience. Each page lives at `resources/views/marketing/for-{slug}.blade.php`. On most pages the existing signature is the accent gradient on the seven section headings plus a motif rendered in exactly three full-bleed layers (hero art, dark band, finale); the briefs below extend it deeper into the page. Shared ground rules for every brief: keep the shared es-* skeleton and section order; implement theming inside the page's nonce'd `<style>` block following the `/* For-x "Nickname" styles */` convention; every gradient needs light and dark variants; every animation needs a reduced-motion kill-switch; carry the accent and motif into at least one mid-page moment (bento, stats or week grid) and recolor the hard-coded blue "See all features" link and related-card hovers to the page accent; remove or actually use any dead `es-*-float` class; never use purple, violet, indigo, fuchsia or pink as accents; never use decorative line drawings (outline SVG illustrations of objects or scenes) - see CLAUDE.md; no new dependencies or external assets.

**Restyle progress:** 0 / 31 applied

> **Review status of the last 14 (2026-07-30).** The final 14 pages were rebuilt in one
> parallel pass. Each was verified MECHANICALLY: HTTP 200, `audit-blade.py` reporting 0
> problems (dead classes, undefined page-local classes, orphan descendant rules, forbidden
> colours, em-dashes, reduced-motion kill-switches), and 0 WCAG AA contrast failures in both
> colour modes. They did NOT receive the independent adversarial design/fabrication review
> that the earlier 17 got, because the reviewing pass was cut short by a usage limit. Their
> authors each reported their own accuracy sweep (fabricated features removed, plan tiers
> corrected), but that self-report is unaudited. Worth a human read for design substance and
> feature-claim accuracy before treating them as final. **Every OTHER page rebuilt in this
> campaign (main, features, templates, legal) did get its independent reviewer.**

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | For Musicians | `/for-musicians` | "The Tour Poster" ground-up rebuild |
| ✅ |   | For DJs | `/for-djs` | "The Neon Sign" ground-up rebuild |
| ✅ |   | For Comedians | `/for-comedians` | "The Tight Five" ground-up rebuild |
| ✅ |   | For Circus and Acrobatics | `/for-circus-acrobatics` | "The Center Ring" ground-up rebuild |
| ✅ |   | For Magicians | `/for-magicians` | "Pick a Card" ground-up rebuild |
| ✅ |   | For Spoken Word | `/for-spoken-word` | "The Sign-Up Sheet" ground-up rebuild |
| ✅ |   | For Bars | `/for-bars` | "The Chalkboard" ground-up rebuild |
| ✅ |   | For Nightclubs | `/for-nightclubs` | "The Door" ground-up rebuild |
| ✅ |   | For Music Venues | `/for-music-venues` | "The Running Order" ground-up rebuild |
| ✅ |   | For Theaters | `/for-theaters` | "The Run" ground-up rebuild |
| ✅ |   | For Dance Groups | `/for-dance-groups` | "The Barre" ground-up rebuild |
| ✅ |   | For Theater Performers | `/for-theater-performers` | "The Résumé" ground-up rebuild |
| ✅ |   | For Food Trucks and Vendors | `/for-food-trucks-and-vendors` | "Today's Stop" ground-up rebuild |
| ✅ |   | For Comedy Clubs | `/for-comedy-clubs` | "Friday at Eight" ground-up rebuild |
| ✅ |   | For Restaurants | `/for-restaurants` | "Twenty-Four Covers" ground-up rebuild |
| ✅ |   | For Breweries and Wineries | `/for-breweries-and-wineries` | "Most Nights Are Free" ground-up rebuild |
| ✅ |   | For Art Galleries | `/for-art-galleries` | "Four Evenings" ground-up rebuild |
| ✅ |   | For Community Centers | `/for-community-centers` | "The Gathering Place" ground-up rebuild |
| ✅ |   | For Fitness and Yoga | `/for-fitness-and-yoga` | "The Flow" ground-up rebuild |
| ✅ |   | For Workshop Instructors | `/for-workshop-instructors` | "The Workshop" ground-up rebuild |
| ✅ |   | For Visual Artists | `/for-visual-artists` | "The Studio Wall" ground-up rebuild |
| ✅ |   | For Farmers Markets | `/for-farmers-markets` | "The Market" ground-up rebuild |
| ✅ |   | For Hotels and Resorts | `/for-hotels-and-resorts` | "The Concierge" ground-up rebuild |
| ✅ |   | For Libraries | `/for-libraries` | "The Catalog" ground-up rebuild |
| ✅ |   | For Webinars | `/for-webinars` | "On Air" ground-up rebuild |
| ✅ |   | For Live Concerts | `/for-live-concerts` | "Live On Stage" ground-up rebuild |
| ✅ |   | For Online Classes | `/for-online-classes` | "The Syllabus" ground-up rebuild |
| ✅ |   | For Virtual Conferences | `/for-virtual-conferences` | "The Agenda" ground-up rebuild |
| ✅ |   | For Live Q&A Sessions | `/for-live-qa-sessions` | "The Conversation" ground-up rebuild |
| ✅ |   | For Watch Parties | `/for-watch-parties` | "The Screening" ground-up rebuild |
| ✅ |   | For AI Agents | `/for-ai-agents` | "The Console" ground-up rebuild |

## Comparison / "Alternative" pages (17)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | Compare (hub) | `/compare` | "Head to Head" ground-up rebuild; the 16 singles below share the `compare-single` template, which is still first-wave |
| ✅ |   | Eventbrite Alternative | `/eventbrite-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Luma Alternative | `/luma-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Ticket Tailor Alternative | `/ticket-tailor-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Google Calendar Alternative | `/google-calendar-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Meetup Alternative | `/meetup-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | DICE Alternative | `/dice-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Brown Paper Tickets Alternative | `/brown-paper-tickets-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Splash Alternative | `/splash-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Sched Alternative | `/sched-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Whova Alternative | `/whova-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Accelevents Alternative | `/accelevents-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Tito Alternative | `/tito-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | AddEvent Alternative | `/addevent-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Pretix Alternative | `/pretix-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Humanitix Alternative | `/humanitix-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ |   | Eventzilla Alternative | `/eventzilla-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |

## "Replace" pages (13)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | Replace (hub) | `/replace` | "The Toolbelt" ground-up rebuild; hub for the 12 replacement singles. Hub page; the 12 singles below share the `replace-single` template |
| ✅ |   | Google Forms Replacement | `/google-forms-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Mailchimp Replacement | `/mailchimp-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Canva Replacement | `/canva-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Linktree Replacement | `/linktree-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Google Sheets Replacement | `/google-sheets-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Calendly Replacement | `/calendly-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | SurveyMonkey Replacement | `/surveymonkey-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Doodle Replacement | `/doodle-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | QR Code Generator Replacement | `/qr-code-generator-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Squarespace Replacement | `/squarespace-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Notion Replacement | `/notion-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ |   | Trello Replacement | `/trello-replacement` | Renders the rebuilt `replace-single` "The Swap" template |

## Legal / Policy (4)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | Privacy Policy | `/privacy` | "The Fine Print" family treatment (shared restrained legal set); legal text verbatim |
| ✅ |   | Terms of Service | `/terms-of-service` | "The Fine Print" family treatment; legal text verbatim |
| ✅ |   | Accessibility | `/accessibility` | "The Fine Print" family treatment; legal text verbatim |
| ✅ |   | Self-Hosting Terms of Service | `/self-hosting-terms-of-service` | "The Fine Print" family treatment; legal text verbatim |

## Docs - User Guide (20)

> The docs deliberately share one restrained shell (`config/docs.php` + `<x-docs-page>`, rebuilt July 2026) rather than per-page designs, so no docs page carries an Updated checkmark.

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | Docs Home | `/docs` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Getting Started | `/docs/getting-started` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Creating Schedules | `/docs/creating-schedules` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Schedule Styling | `/docs/schedule-styling` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Managing Schedules | `/docs/managing-schedules` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Creating Events | `/docs/creating-events` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Sharing | `/docs/sharing` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Tickets | `/docs/tickets` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Subscriptions | `/docs/subscriptions` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Gift Cards | `/docs/gift-cards` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Allocated Seating | `/docs/allocated-seating` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Appointments | `/docs/appointments` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Event Graphics | `/docs/event-graphics` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Newsletters | `/docs/newsletters` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Analytics | `/docs/analytics` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Account Settings | `/docs/account-settings` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Boost | `/docs/boost` | Accuracy pass (shared docs shell by design) |
| ✅ |   | AI Import | `/docs/ai-import` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Scan Agenda | `/docs/scan-agenda` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Referral Program | `/docs/referral-program` | Accuracy pass (shared docs shell by design) |

## Docs - Selfhost (11)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | Selfhost Overview | `/docs/selfhost` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Installation | `/docs/selfhost/installation` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Stripe (Selfhost) | `/docs/selfhost/stripe` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Google Calendar (Selfhost) | `/docs/selfhost/google-calendar` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Microsoft Calendar (Selfhost) | `/docs/selfhost/microsoft-calendar` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Boost (Selfhost) | `/docs/selfhost/boost` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Admin (Selfhost) | `/docs/selfhost/admin` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Federation (Selfhost) | `/docs/selfhost/federation` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Email (Selfhost) | `/docs/selfhost/email` | Accuracy pass (shared docs shell by design) |
| ✅ |   | AI (Selfhost) | `/docs/selfhost/ai` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Accessibility (Selfhost) | `/docs/selfhost/accessibility` | Accuracy pass (shared docs shell by design) |

## Docs - SaaS (5)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | SaaS Setup | `/docs/saas` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Custom Domains (SaaS) | `/docs/saas/custom-domains` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Twilio (SaaS) | `/docs/saas/twilio` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Federation (SaaS) | `/docs/saas/federation` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Monetization (SaaS) | `/docs/saas/monetization` | Accuracy pass (shared docs shell by design) |

## Docs - Developer (2)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ |   | API | `/docs/developer/api` | Accuracy pass (shared docs shell by design) |
| ✅ |   | Webhooks | `/docs/developer/webhooks` | Accuracy pass (shared docs shell by design) |

## Dynamic pages (not counted in the 151)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| | | Blog | `/blog` | Dynamic / DB-driven index (no static Blade view); listed for completeness |
