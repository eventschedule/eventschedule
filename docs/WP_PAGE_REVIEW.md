# WP Page Review Tracker

A checklist of every WP (marketing) site page, used to track review progress as each page is reviewed.

**Legend:** a checkmark in the **Reviewed** column marks a page as reviewed; a blank cell means it has not been reviewed yet. The **Updated** column marks pages that have a page-exclusive, ground-up design - either motif-driven (a large page-local `<style>` block with its own class namespace, headed by a `/* ... "Nickname" ... */` concept comment) or component-driven (restructured onto the shared components in `resources/views/components/marketing/`). A blank cell means the page is still on the first-wave es-* skeleton with a themed `.text-gradient-<page>` accent and is a candidate for a rebuild; the Notes cell of an updated page names its design. **The docs tables are the deliberate exception:** the 37 documentation pages share ONE restrained shell on purpose so the manual reads as a single book, so ✅ there records the July 2026 *accuracy and legibility* pass (every capability claim re-verified against code, stale UI labels fixed, contrast measured) rather than a page-exclusive design. On the audience "For" pages table, Updated also means that page's restyle brief (the Notes cell) has been applied. The **Verified** column, which exists only on the two audience tables, records the July 2026 faithfulness audit: every page was read in full against its brief's Accent / Motif / Sections / Distinct targets; a plain ✅ means the brief was already fully realized, and a ✅ with a short note flags a small gap that this pass fixed.

**Progress:** 151 / 151 reviewed

**Updated:** 151 / 151 rebuilt

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

> Scope: static and functional marketing pages served under `marketing.*` routes (`routes/web.php`, `MarketingController`), cross-checked against `resources/views/sitemap.blade.php`. Excludes URL redirects, the shared partials/components, and individual blog posts. The comparison and replacement detail pages each render one shared template driven by per-slug data.

## Main / Top-level (14)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Homepage | `/` | Homepage-exclusive design: live poster wall, 3D showcase, pinned gallery rail, integrations orbit |
| ✅ | ✅ | Features | `/features` | Rebuilt as five chapters via `<x-marketing.feature-chapter>` / `<x-marketing.feature-banner>` |
| ✅ | ✅ | Pricing | `/pricing` | Rebuilt July 2026 (the feature lists are curated - see CLAUDE.md before editing them) |
| ✅ | ✅ | About | `/about` | "The Colophon" ground-up rebuild |
| ✅ | ✅ | Demos / Examples | `/examples` | "The Showroom" ground-up rebuild; route name is `marketing.demos` |
| ✅ | ✅ | Search | `/search` | "The Lookup" ground-up rebuild; functional page, search form and query handling preserved |
| ✅ | ✅ | Browse | `/browse` | "The Newsstand" ground-up rebuild; functional page, filters and query params preserved |
| ✅ | ✅ | FAQ | `/faq` | "The Front Desk" ground-up rebuild |
| ✅ | ✅ | Why Create an Account | `/why-create-account` | "The Keyring" ground-up rebuild |
| ✅ | ✅ | Use Cases | `/use-cases` | Component-driven directory via `<x-marketing.audience-card>` |
| ✅ | ✅ | Contact | `/contact` | "The Postcard" ground-up rebuild |
| ✅ | ✅ | Open Source | `/open-source` | "The Commit Log" ground-up rebuild |
| ✅ | ✅ | Selfhost | `/selfhost` | "The Terminal" ground-up rebuild |
| ✅ | ✅ | SaaS | `/saas` | "The Stack" ground-up rebuild; white-label SaaS operator landing |

## Feature pages (32)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Ticketing | `/features/ticketing` | "The Turnstile" ground-up rebuild |
| ✅ | ✅ | Gift Cards | `/features/gift-cards` | "The Gift Envelope" ground-up rebuild |
| ✅ | ✅ | AI | `/features/ai` | "The Spark" ground-up rebuild |
| ✅ | ✅ | Calendar Sync | `/features/calendar-sync` | "The Round Trip" ground-up rebuild (renamed from "The Loop", which recurring-events holds) |
| ✅ | ✅ | Analytics | `/features/analytics` | "The Dashboard" ground-up rebuild |
| ✅ | ✅ | Integrations | `/features/integrations` | "The Wire" ground-up rebuild |
| ✅ | ✅ | Custom Fields | `/features/custom-fields` | "The Form" ground-up rebuild |
| ✅ | ✅ | Custom Labels | `/features/custom-labels` | "The Rename" ground-up rebuild |
| ✅ | ✅ | Team Scheduling | `/features/team-scheduling` | "The Lineup" ground-up rebuild |
| ✅ | ✅ | Sub-schedules | `/features/sub-schedules` | "The Sort" ground-up rebuild |
| ✅ | ✅ | Online Events | `/features/online-events` | "Go Live" ground-up rebuild |
| ✅ | ✅ | Newsletters | `/features/newsletters` | "The Send" ground-up rebuild |
| ✅ | ✅ | Recurring Events | `/features/recurring-events` | "The Loop" ground-up rebuild |
| ✅ | ✅ | Embed Calendar | `/features/embed-calendar` | "The Paste" ground-up rebuild |
| ✅ | ✅ | Embed Tickets | `/features/embed-tickets` | "The Widget" ground-up rebuild |
| ✅ | ✅ | Fan Videos | `/features/fan-videos` | "The Reel" ground-up rebuild |
| ✅ | ✅ | Polls | `/features/polls` | "The Vote" ground-up rebuild |
| ✅ | ✅ | Boost | `/features/boost` | "The Launch" ground-up rebuild |
| ✅ | ✅ | Private Events | `/features/private-events` | "The Vault" ground-up rebuild |
| ✅ | ✅ | Event Graphics | `/features/event-graphics` | "The Gallery" ground-up rebuild |
| ✅ | ✅ | White Label | `/features/white-label` | "The Blank Slate" ground-up rebuild |
| ✅ | ✅ | Custom CSS | `/features/custom-css` | "The Stylesheet" ground-up rebuild |
| ✅ | ✅ | Custom Domain | `/features/custom-domain` | "The Nameplate" ground-up rebuild |
| ✅ | ✅ | Feedback | `/features/feedback` | "The Comment Card" ground-up rebuild |
| ✅ | ✅ | Availability | `/features/availability` | "Office Hours" ground-up rebuild |
| ✅ | ✅ | Appointments | `/features/appointments` | "The Appointment Book" ground-up rebuild (replaces the shared gift-cards skeleton) |
| ✅ | ✅ | Carpool | `/features/carpool` | "Four Seats" ground-up rebuild |
| ✅ | ✅ | Google Calendar | `/google-calendar` | "The Invitation" ground-up rebuild; root-level integration page |
| ✅ | ✅ | Outlook Calendar | `/outlook-calendar` | "The Meeting Request" ground-up rebuild; root-level integration page |
| ✅ | ✅ | CalDAV | `/caldav` | "The Open Protocol" ground-up rebuild; root-level integration page |
| ✅ | ✅ | Stripe | `/stripe` | "The Payout" ground-up rebuild; root-level integration page |
| ✅ | ✅ | Invoice Ninja | `/invoiceninja` | "The Ledger" ground-up rebuild; root-level integration page |

## Schedule Type Hubs (3)

> The three top-level audience pages, one per schedule type (Talent, Venue, Curator). Each lives at `resources/views/marketing/for-{slug}.blade.php`.

| Reviewed | Updated | Verified | Page | URL | Notes |
|:--------:|:-------:|:-------:|------|-----|-------|
| ✅ | ✅ | ✅ | For Talent | `/for-talent` | Rebuilt July 2026 to match `/`, `/features`, `/pricing` and `/use-cases`: brand blue→sky→cyan (retiring the amber/rose "Center Stage" gels), hero schedule mockup, a five-banner "Life of a gig" run via `<x-marketing.feature-banner>`, a `#live` rail of real talent events (hides below 4), the `#keep` fee band, an accurate free/Pro split, and the 12 performer cards via `<x-marketing.audience-card>` off the shared `config/marketing_audiences.php`. |
| ✅ | ✅ | ✅ | For Venues | `/for-venues` | "Front of House" ground-up rebuild, July 2026. Two acts: front of house (calendar, box office, own brand, private hire) and back of house (booking inbox, the door, rooms, the numbers), hinged by a full-bleed "pass door" that carries the mid-page CTA. The two acts read through copy, ground colour and lighting - the facade-elevation and floor-plan line drawings that originally carried the motif were removed in the July 2026 line-drawing sweep. Act 02 uses the new `ground="dark"` on `<x-marketing.feature-banner>`/`feature-chapter` (fixed dark in both colour modes). Adds a week board, a "stack this replaces" row linking seven `replace`/`compare` pages, a venue-specific plan strip reading prices from config, `HowTo` schema, and the 12 venue cards via `<x-marketing.audience-card>` off the shared `config/marketing_audiences.php`. Fixed a wrong FAQ answer that claimed a "door staff" role. |
| ✅ | ✅ | ✅ fixed 2 headings | For Curators | `/for-curators` | "The Listings" ground-up rebuild |

## Audience "For" pages (31)

> **Restyle briefs.** Each note below can be pasted into Claude as the instruction for making that page's design more unique to its audience. Each page lives at `resources/views/marketing/for-{slug}.blade.php`. On most pages the existing signature is the accent gradient on the seven section headings plus a motif rendered in exactly three full-bleed layers (hero art, dark band, finale); the briefs below extend it deeper into the page. Shared ground rules for every brief: keep the shared es-* skeleton and section order; implement theming inside the page's nonce'd `<style>` block following the `/* For-x "Nickname" styles */` convention; every gradient needs light and dark variants; every animation needs a reduced-motion kill-switch; carry the accent and motif into at least one mid-page moment (bento, stats or week grid) and recolor the hard-coded blue "See all features" link and related-card hovers to the page accent; remove or actually use any dead `es-*-float` class; never use purple, violet, indigo, fuchsia or pink as accents; never use decorative line drawings (outline SVG illustrations of objects or scenes) - see CLAUDE.md; no new dependencies or external assets.

**Restyle progress:** 31 / 31 applied

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

**Verified (Jul 2026 audit):** 31 / 31 audited faithful to their briefs; 8 pages received small brief-completion fixes (see the Verified column).

| Reviewed | Updated | Verified | Page | URL | Notes |
|:--------:|:-------:|:-------:|------|-----|-------|
| ✅ | ✅ | ✅ | For Musicians | `/for-musicians` | "The Tour Poster" ground-up rebuild |
| ✅ | ✅ | ✅ rendered dead class | For DJs | `/for-djs` | "The Neon Sign" ground-up rebuild |
| ✅ | ✅ | ✅ | For Comedians | `/for-comedians` | "The Tight Five" ground-up rebuild |
| ✅ | ✅ | ✅ | For Circus and Acrobatics | `/for-circus-acrobatics` | "The Center Ring" ground-up rebuild |
| ✅ | ✅ | ✅ | For Magicians | `/for-magicians` | "Pick a Card" ground-up rebuild |
| ✅ | ✅ | ✅ | For Spoken Word | `/for-spoken-word` | "The Sign-Up Sheet" ground-up rebuild |
| ✅ | ✅ | ✅ FAQ accent hover | For Bars | `/for-bars` | "The Chalkboard" ground-up rebuild |
| ✅ | ✅ | ✅ | For Nightclubs | `/for-nightclubs` | "The Door" ground-up rebuild |
| ✅ | ✅ | ✅ | For Music Venues | `/for-music-venues` | "The Running Order" ground-up rebuild |
| ✅ | ✅ | ✅ added season pass | For Theaters | `/for-theaters` | "The Run" ground-up rebuild |
| ✅ | ✅ | ✅ | For Dance Groups | `/for-dance-groups` | "The Barre" ground-up rebuild |
| ✅ | ✅ | ✅ | For Theater Performers | `/for-theater-performers` | "The Résumé" ground-up rebuild |
| ✅ | ✅ | ✅ finale motif added | For Food Trucks and Vendors | `/for-food-trucks-and-vendors` | "Today's Stop" ground-up rebuild |
| ✅ | ✅ | ✅ | For Comedy Clubs | `/for-comedy-clubs` | "Friday at Eight" ground-up rebuild |
| ✅ | ✅ | ✅ course labels | For Restaurants | `/for-restaurants` | "Twenty-Four Covers" ground-up rebuild |
| ✅ | ✅ | ✅ FAQ accent hover | For Breweries and Wineries | `/for-breweries-and-wineries` | "Most Nights Are Free" ground-up rebuild |
| ✅ | ✅ | ✅ finale aurora recolored | For Art Galleries | `/for-art-galleries` | "Four Evenings" ground-up rebuild |
| ✅ | ✅ | ✅ | For Community Centers | `/for-community-centers` | "The Gathering Place" ground-up rebuild |
| ✅ | ✅ | ✅ | For Fitness and Yoga | `/for-fitness-and-yoga` | "The Flow" ground-up rebuild |
| ✅ | ✅ | ✅ | For Workshop Instructors | `/for-workshop-instructors` | "The Workshop" ground-up rebuild |
| ✅ | ✅ | ✅ | For Visual Artists | `/for-visual-artists` | "The Studio Wall" ground-up rebuild |
| ✅ | ✅ | ✅ | For Farmers Markets | `/for-farmers-markets` | "The Market" ground-up rebuild |
| ✅ | ✅ | ✅ | For Hotels and Resorts | `/for-hotels-and-resorts` | "The Concierge" ground-up rebuild |
| ✅ | ✅ | ✅ comment + dark vars | For Libraries | `/for-libraries` | "The Catalog" ground-up rebuild |
| ✅ | ✅ | ✅ | For Webinars | `/for-webinars` | "On Air" ground-up rebuild |
| ✅ | ✅ | ✅ | For Live Concerts | `/for-live-concerts` | "Live On Stage" ground-up rebuild |
| ✅ | ✅ | ✅ | For Online Classes | `/for-online-classes` | "The Syllabus" ground-up rebuild |
| ✅ | ✅ | ✅ | For Virtual Conferences | `/for-virtual-conferences` | "The Agenda" ground-up rebuild |
| ✅ | ✅ | ✅ | For Live Q&A Sessions | `/for-live-qa-sessions` | "The Conversation" ground-up rebuild |
| ✅ | ✅ | ✅ | For Watch Parties | `/for-watch-parties` | "The Screening" ground-up rebuild |
| ✅ | ✅ | ✅ | For AI Agents | `/for-ai-agents` | "The Console" ground-up rebuild |

## Comparison / "Alternative" pages (17)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Compare (hub) | `/compare` | "Head to Head" ground-up rebuild; the 16 singles below share the `compare-single` template, which is still first-wave |
| ✅ | ✅ | Eventbrite Alternative | `/eventbrite-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Luma Alternative | `/luma-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Ticket Tailor Alternative | `/ticket-tailor-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Google Calendar Alternative | `/google-calendar-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Meetup Alternative | `/meetup-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | DICE Alternative | `/dice-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Brown Paper Tickets Alternative | `/brown-paper-tickets-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Splash Alternative | `/splash-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Sched Alternative | `/sched-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Whova Alternative | `/whova-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Accelevents Alternative | `/accelevents-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Tito Alternative | `/tito-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | AddEvent Alternative | `/addevent-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Pretix Alternative | `/pretix-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Humanitix Alternative | `/humanitix-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |
| ✅ | ✅ | Eventzilla Alternative | `/eventzilla-alternative` | Renders the rebuilt `compare-single` "The Scorecard" template |

## "Replace" pages (13)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Replace (hub) | `/replace` | "The Toolbelt" ground-up rebuild; hub for the 12 replacement singles. Hub page; the 12 singles below share the `replace-single` template |
| ✅ | ✅ | Google Forms Replacement | `/google-forms-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Mailchimp Replacement | `/mailchimp-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Canva Replacement | `/canva-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Linktree Replacement | `/linktree-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Google Sheets Replacement | `/google-sheets-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Calendly Replacement | `/calendly-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | SurveyMonkey Replacement | `/surveymonkey-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Doodle Replacement | `/doodle-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | QR Code Generator Replacement | `/qr-code-generator-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Squarespace Replacement | `/squarespace-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Notion Replacement | `/notion-replacement` | Renders the rebuilt `replace-single` "The Swap" template |
| ✅ | ✅ | Trello Replacement | `/trello-replacement` | Renders the rebuilt `replace-single` "The Swap" template |

## Legal / Policy (4)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Privacy Policy | `/privacy` | "The Fine Print" family treatment (shared restrained legal set); legal text verbatim |
| ✅ | ✅ | Terms of Service | `/terms-of-service` | "The Fine Print" family treatment; legal text verbatim |
| ✅ | ✅ | Accessibility | `/accessibility` | "The Fine Print" family treatment; legal text verbatim |
| ✅ | ✅ | Self-Hosting Terms of Service | `/self-hosting-terms-of-service` | "The Fine Print" family treatment; legal text verbatim |

## Docs - User Guide (19)

> The docs deliberately share one restrained shell (`config/docs.php` + `<x-docs-page>`, rebuilt July 2026) rather than per-page designs, so no docs page carries an Updated checkmark.

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Docs Home | `/docs` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Getting Started | `/docs/getting-started` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Creating Schedules | `/docs/creating-schedules` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Schedule Styling | `/docs/schedule-styling` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Managing Schedules | `/docs/managing-schedules` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Creating Events | `/docs/creating-events` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Sharing | `/docs/sharing` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Tickets | `/docs/tickets` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Subscriptions | `/docs/subscriptions` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Gift Cards | `/docs/gift-cards` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Appointments | `/docs/appointments` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Event Graphics | `/docs/event-graphics` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Newsletters | `/docs/newsletters` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Analytics | `/docs/analytics` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Account Settings | `/docs/account-settings` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Boost | `/docs/boost` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | AI Import | `/docs/ai-import` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Scan Agenda | `/docs/scan-agenda` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Referral Program | `/docs/referral-program` | Accuracy pass (shared docs shell by design) |

## Docs - Selfhost (11)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | Selfhost Overview | `/docs/selfhost` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Installation | `/docs/selfhost/installation` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Stripe (Selfhost) | `/docs/selfhost/stripe` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Google Calendar (Selfhost) | `/docs/selfhost/google-calendar` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Microsoft Calendar (Selfhost) | `/docs/selfhost/microsoft-calendar` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Boost (Selfhost) | `/docs/selfhost/boost` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Admin (Selfhost) | `/docs/selfhost/admin` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Federation (Selfhost) | `/docs/selfhost/federation` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Email (Selfhost) | `/docs/selfhost/email` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | AI (Selfhost) | `/docs/selfhost/ai` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Accessibility (Selfhost) | `/docs/selfhost/accessibility` | Accuracy pass (shared docs shell by design) |

## Docs - SaaS (5)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | SaaS Setup | `/docs/saas` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Custom Domains (SaaS) | `/docs/saas/custom-domains` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Twilio (SaaS) | `/docs/saas/twilio` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Federation (SaaS) | `/docs/saas/federation` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Monetization (SaaS) | `/docs/saas/monetization` | Accuracy pass (shared docs shell by design) |

## Docs - Developer (2)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | ✅ | API | `/docs/developer/api` | Accuracy pass (shared docs shell by design) |
| ✅ | ✅ | Webhooks | `/docs/developer/webhooks` | Accuracy pass (shared docs shell by design) |

## Dynamic pages (not counted in the 151)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| | | Blog | `/blog` | Dynamic / DB-driven index (no static Blade view); listed for completeness |
