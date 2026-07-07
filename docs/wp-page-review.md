# WP Page Review Tracker

A checklist of every WP (marketing) site page, used to track review progress as each page is reviewed.

**Legend:** a checkmark in the **Reviewed** column marks a page as reviewed; a blank cell means it has not been reviewed yet.

**Progress:** 1 / 142 reviewed

> Scope: static and functional marketing pages served under `marketing.*` routes (`routes/web.php`, `MarketingController`), cross-checked against `resources/views/sitemap.blade.php`. Excludes URL redirects, the shared partials/components, and individual blog posts. The comparison and replacement detail pages each render one shared template driven by per-slug data.

## Main / Top-level (14)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| ✅ | Homepage | `/` | |
| | Features | `/features` | |
| | Pricing | `/pricing` | |
| | About | `/about` | |
| | Demos / Examples | `/examples` | Route name is `marketing.demos` |
| | Search | `/search` | Functional page |
| | Browse | `/browse` | Functional page |
| | FAQ | `/faq` | |
| | Why Create an Account | `/why-create-account` | |
| | Use Cases | `/use-cases` | |
| | Contact | `/contact` | |
| | Open Source | `/open-source` | |
| | Selfhost | `/selfhost` | |
| | SaaS | `/saas` | White-label SaaS operator landing |

## Feature pages (29)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Ticketing | `/features/ticketing` | |
| | AI | `/features/ai` | |
| | Calendar Sync | `/features/calendar-sync` | |
| | Analytics | `/features/analytics` | |
| | Integrations | `/features/integrations` | |
| | Custom Fields | `/features/custom-fields` | |
| | Custom Labels | `/features/custom-labels` | |
| | Team Scheduling | `/features/team-scheduling` | |
| | Sub-schedules | `/features/sub-schedules` | |
| | Online Events | `/features/online-events` | |
| | Newsletters | `/features/newsletters` | |
| | Recurring Events | `/features/recurring-events` | |
| | Embed Calendar | `/features/embed-calendar` | |
| | Embed Tickets | `/features/embed-tickets` | |
| | Fan Videos | `/features/fan-videos` | |
| | Polls | `/features/polls` | |
| | Boost | `/features/boost` | |
| | Private Events | `/features/private-events` | |
| | Event Graphics | `/features/event-graphics` | |
| | White Label | `/features/white-label` | |
| | Custom CSS | `/features/custom-css` | |
| | Custom Domain | `/features/custom-domain` | |
| | Feedback | `/features/feedback` | |
| | Availability | `/features/availability` | |
| | Carpool | `/features/carpool` | |
| | Google Calendar | `/google-calendar` | Root-level integration page |
| | CalDAV | `/caldav` | Root-level integration page |
| | Stripe | `/stripe` | Root-level integration page |
| | Invoice Ninja | `/invoiceninja` | Root-level integration page |

## Audience "For" pages (34)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | For Talent | `/for-talent` | |
| | For Venues | `/for-venues` | |
| | For Curators | `/for-curators` | |
| | For Musicians | `/for-musicians` | |
| | For DJs | `/for-djs` | |
| | For Comedians | `/for-comedians` | |
| | For Circus and Acrobatics | `/for-circus-acrobatics` | |
| | For Magicians | `/for-magicians` | |
| | For Spoken Word | `/for-spoken-word` | |
| | For Bars | `/for-bars` | |
| | For Nightclubs | `/for-nightclubs` | |
| | For Music Venues | `/for-music-venues` | |
| | For Theaters | `/for-theaters` | |
| | For Dance Groups | `/for-dance-groups` | |
| | For Theater Performers | `/for-theater-performers` | |
| | For Food Trucks and Vendors | `/for-food-trucks-and-vendors` | |
| | For Comedy Clubs | `/for-comedy-clubs` | |
| | For Restaurants | `/for-restaurants` | |
| | For Breweries and Wineries | `/for-breweries-and-wineries` | |
| | For Art Galleries | `/for-art-galleries` | |
| | For Community Centers | `/for-community-centers` | |
| | For Fitness and Yoga | `/for-fitness-and-yoga` | |
| | For Workshop Instructors | `/for-workshop-instructors` | |
| | For Visual Artists | `/for-visual-artists` | |
| | For Farmers Markets | `/for-farmers-markets` | |
| | For Hotels and Resorts | `/for-hotels-and-resorts` | |
| | For Libraries | `/for-libraries` | |
| | For Webinars | `/for-webinars` | |
| | For Live Concerts | `/for-live-concerts` | |
| | For Online Classes | `/for-online-classes` | |
| | For Virtual Conferences | `/for-virtual-conferences` | |
| | For Live Q&A Sessions | `/for-live-qa-sessions` | |
| | For Watch Parties | `/for-watch-parties` | |
| | For AI Agents | `/for-ai-agents` | |

## Comparison / "Alternative" pages (17)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Compare (hub) | `/compare` | Hub page; the 16 singles below share the `compare-single` template |
| | Eventbrite Alternative | `/eventbrite-alternative` | |
| | Luma Alternative | `/luma-alternative` | |
| | Ticket Tailor Alternative | `/ticket-tailor-alternative` | |
| | Google Calendar Alternative | `/google-calendar-alternative` | |
| | Meetup Alternative | `/meetup-alternative` | |
| | DICE Alternative | `/dice-alternative` | |
| | Brown Paper Tickets Alternative | `/brown-paper-tickets-alternative` | |
| | Splash Alternative | `/splash-alternative` | |
| | Sched Alternative | `/sched-alternative` | |
| | Whova Alternative | `/whova-alternative` | |
| | Accelevents Alternative | `/accelevents-alternative` | |
| | Tito Alternative | `/tito-alternative` | |
| | AddEvent Alternative | `/addevent-alternative` | |
| | Pretix Alternative | `/pretix-alternative` | |
| | Humanitix Alternative | `/humanitix-alternative` | |
| | Eventzilla Alternative | `/eventzilla-alternative` | |

## "Replace" pages (13)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Replace (hub) | `/replace` | Hub page; the 12 singles below share the `replace-single` template |
| | Google Forms Replacement | `/google-forms-replacement` | |
| | Mailchimp Replacement | `/mailchimp-replacement` | |
| | Canva Replacement | `/canva-replacement` | |
| | Linktree Replacement | `/linktree-replacement` | |
| | Google Sheets Replacement | `/google-sheets-replacement` | |
| | Calendly Replacement | `/calendly-replacement` | |
| | SurveyMonkey Replacement | `/surveymonkey-replacement` | |
| | Doodle Replacement | `/doodle-replacement` | |
| | QR Code Generator Replacement | `/qr-code-generator-replacement` | |
| | Squarespace Replacement | `/squarespace-replacement` | |
| | Notion Replacement | `/notion-replacement` | |
| | Trello Replacement | `/trello-replacement` | |

## Legal / Policy (4)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Privacy Policy | `/privacy` | |
| | Terms of Service | `/terms-of-service` | |
| | Accessibility | `/accessibility` | |
| | Self-Hosting Terms of Service | `/self-hosting-terms-of-service` | |

## Docs - User Guide (17)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Docs Home | `/docs` | |
| | Getting Started | `/docs/getting-started` | |
| | Creating Schedules | `/docs/creating-schedules` | |
| | Schedule Styling | `/docs/schedule-styling` | |
| | Managing Schedules | `/docs/managing-schedules` | |
| | Creating Events | `/docs/creating-events` | |
| | Sharing | `/docs/sharing` | |
| | Tickets | `/docs/tickets` | |
| | Subscriptions | `/docs/subscriptions` | |
| | Event Graphics | `/docs/event-graphics` | |
| | Newsletters | `/docs/newsletters` | |
| | Analytics | `/docs/analytics` | |
| | Account Settings | `/docs/account-settings` | |
| | Boost | `/docs/boost` | |
| | AI Import | `/docs/ai-import` | |
| | Scan Agenda | `/docs/scan-agenda` | |
| | Referral Program | `/docs/referral-program` | |

## Docs - Selfhost (9)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Selfhost Overview | `/docs/selfhost` | |
| | Installation | `/docs/selfhost/installation` | |
| | Stripe (Selfhost) | `/docs/selfhost/stripe` | |
| | Google Calendar (Selfhost) | `/docs/selfhost/google-calendar` | |
| | Boost (Selfhost) | `/docs/selfhost/boost` | |
| | Admin (Selfhost) | `/docs/selfhost/admin` | |
| | Email (Selfhost) | `/docs/selfhost/email` | |
| | AI (Selfhost) | `/docs/selfhost/ai` | |
| | Accessibility (Selfhost) | `/docs/selfhost/accessibility` | |

## Docs - SaaS (3)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | SaaS Setup | `/docs/saas` | |
| | Custom Domains (SaaS) | `/docs/saas/custom-domains` | |
| | Twilio (SaaS) | `/docs/saas/twilio` | |

## Docs - Developer (2)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | API | `/docs/developer/api` | |
| | Webhooks | `/docs/developer/webhooks` | |

## Dynamic pages (not counted in the 142)

| Reviewed | Page | URL | Notes |
|:--------:|------|-----|-------|
| | Blog | `/blog` | Dynamic / DB-driven index (no static Blade view); listed for completeness |
