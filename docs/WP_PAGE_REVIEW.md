# WP Page Review Tracker

A checklist of every WP (marketing) site page, used to track review progress as each page is reviewed.

**Legend:** a checkmark in the **Reviewed** column marks a page as reviewed; a blank cell means it has not been reviewed yet. The **Updated** column marks pages that have a page-exclusive, ground-up design - either motif-driven (a large page-local `<style>` block with its own class namespace, headed by a `/* ... "Nickname" ... */` concept comment) or component-driven (restructured onto the shared components in `resources/views/components/marketing/`). A blank cell means the page is still on the first-wave es-* skeleton with a themed `.text-gradient-<page>` accent and is a candidate for a rebuild; the Notes cell of an updated page names its design. On the audience "For" pages table, Updated also means that page's restyle brief (the Notes cell) has been applied. The **Verified** column, which exists only on the two audience tables, records the July 2026 faithfulness audit: every page was read in full against its brief's Accent / Motif / Sections / Distinct targets; a plain ✅ means the brief was already fully realized, and a ✅ with a short note flags a small gap that this pass fixed.

**Progress:** 147 / 147 reviewed

**Updated:** 114 / 147 rebuilt

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
| ✅ | | Docs Home | `/docs` | |
| ✅ | | Getting Started | `/docs/getting-started` | |
| ✅ | | Creating Schedules | `/docs/creating-schedules` | |
| ✅ | | Schedule Styling | `/docs/schedule-styling` | |
| ✅ | | Managing Schedules | `/docs/managing-schedules` | |
| ✅ | | Creating Events | `/docs/creating-events` | |
| ✅ | | Sharing | `/docs/sharing` | |
| ✅ | | Tickets | `/docs/tickets` | |
| ✅ | | Subscriptions | `/docs/subscriptions` | |
| ✅ | | Gift Cards | `/docs/gift-cards` | |
| ✅ | | Appointments | `/docs/appointments` | |
| ✅ | | Event Graphics | `/docs/event-graphics` | |
| ✅ | | Newsletters | `/docs/newsletters` | |
| ✅ | | Analytics | `/docs/analytics` | |
| ✅ | | Account Settings | `/docs/account-settings` | |
| ✅ | | Boost | `/docs/boost` | |
| ✅ | | AI Import | `/docs/ai-import` | |
| ✅ | | Scan Agenda | `/docs/scan-agenda` | |
| ✅ | | Referral Program | `/docs/referral-program` | |

## Docs - Selfhost (9)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | | Selfhost Overview | `/docs/selfhost` | |
| ✅ | | Installation | `/docs/selfhost/installation` | |
| ✅ | | Stripe (Selfhost) | `/docs/selfhost/stripe` | |
| ✅ | | Google Calendar (Selfhost) | `/docs/selfhost/google-calendar` | |
| ✅ | | Boost (Selfhost) | `/docs/selfhost/boost` | |
| ✅ | | Admin (Selfhost) | `/docs/selfhost/admin` | |
| ✅ | | Email (Selfhost) | `/docs/selfhost/email` | |
| ✅ | | AI (Selfhost) | `/docs/selfhost/ai` | |
| ✅ | | Accessibility (Selfhost) | `/docs/selfhost/accessibility` | |

## Docs - SaaS (3)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | | SaaS Setup | `/docs/saas` | |
| ✅ | | Custom Domains (SaaS) | `/docs/saas/custom-domains` | |
| ✅ | | Twilio (SaaS) | `/docs/saas/twilio` | |

## Docs - Developer (2)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| ✅ | | API | `/docs/developer/api` | |
| ✅ | | Webhooks | `/docs/developer/webhooks` | |

## Dynamic pages (not counted in the 147)

| Reviewed | Updated | Page | URL | Notes |
|:--------:|:-------:|------|-----|-------|
| | | Blog | `/blog` | Dynamic / DB-driven index (no static Blade view); listed for completeness |
