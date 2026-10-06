<div align="center">
    <picture>
        <source srcset="public/images/light_logo.png" media="(prefers-color-scheme: dark)">
        <img src="public/images/dark_logo.png" alt="Event Schedule" width="350">
    </picture>
    <p>
        An open-source platform to share events, sell tickets and bring communities together.
    </p>
    <p>
        <a href="https://github.com/eventschedule/eventschedule/releases/latest"><img src="https://img.shields.io/github/v/release/eventschedule/eventschedule?label=release" alt="Latest release"></a>
        <a href="LICENSE"><img src="https://img.shields.io/badge/license-AAL-blue" alt="License: AAL"></a>
        <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
        <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" alt="Laravel 11">
    </p>
    <p>
        <a href="https://eventschedule.com">Website</a> &middot;
        <a href="https://eventschedule.com/docs">Docs</a> &middot;
        <a href="https://eventschedule.com/docs/selfhost">Selfhost guide</a> &middot;
        <a href="https://eventschedule.com/examples">Examples</a> &middot;
        <a href="https://github.com/eventschedule/eventschedule/issues">Issues</a>
    </p>
</div>

Event Schedule gives a venue, a performer or a community one place to publish a calendar, take registrations, sell tickets and reach the people who follow them. Run it on your own server with every feature included, or use the hosted version at [eventschedule.com](https://eventschedule.com).

<div align="center">
    <a href="https://www.youtube.com/watch?v=w1JLIvGmIjQ">
        <img src="https://img.youtube.com/vi/w1JLIvGmIjQ/maxresdefault.jpg" alt="Watch the overview" width="600">
    </a>
    <p>Watch the overview</p>
</div>

## Screenshots

<p align="center">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_1.png?raw=true" width="49%" alt="Guest > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_2.png?raw=true" width="49%" alt="Guest > Event">
</p>
<p align="center">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_3.png?raw=true" width="49%" alt="Admin > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_4.png?raw=true" width="49%" alt="Admin > Event">
</p>

## Why Event Schedule?

- **Everything is unlocked.** Every paid-plan feature of the hosted version is switched on in a selfhosted install. No license keys, no plan tiers, and no platform fees on ticket sales.
- **No telemetry.** No usage reporting and no license check. JS and CSS libraries and fonts are vendored in the repo instead of pulled from a CDN. See [what a fresh install connects to](#what-a-fresh-install-connects-to).
- **Easy to deploy.** Install with Docker, Softaculous or by hand. A browser setup wizard tests the database connection, runs the migrations and writes the config. Updates are one click from the admin panel, migrations included.
- **Bring your own everything.** SMTP, Stripe or PayPal, Google and Microsoft credentials, Gemini or OpenAI keys. Each one is an [environment variable](#configuration), and the feature stays completely out of the way until you set it.
- **Or skip the server.** The same app runs at [eventschedule.com](https://eventschedule.com), with a free plan and nothing to maintain.

## Features

### 🗓️ Events and calendars

- **Unlimited schedules and events:** Sub-schedules, categories, search and filters.
- **Recurring events:** Daily, weekly, every N weeks, monthly by date or by weekday (the 2nd Tuesday), and yearly, with per-date exceptions.
- **Three schedule types:** Venue, talent and curator. A curator schedule automatically pulls in the events of the venues and performers it lists.
- **Visibility:** Public, draft, internal (members only) and unlisted events, the last reachable by link with an optional password.
- **Event requests:** Let the public submit events to your schedule, with an approval queue.
- **Free registration:** RSVP for free events with optional capacity limits, for in-person and online events alike.
- **Extras:** Agendas, templates, cloning, custom fields and polls.
- **Sharing:** Embeddable calendars, iCal and RSS feeds, .ics downloads, and add-to-calendar links for Google, Apple and Microsoft.

### 🎟️ Ticketing

- **Direct payments:** Paid tickets through Stripe, PayPal, Payfast, Invoice Ninja, payment links or cash, straight into your own accounts. The payment processor's fee is the only fee. Full and partial refunds.
- **Advanced options:** Multiple ticket types, sales windows, group discounts, promo codes, add-ons, waitlists, multi-event carts, installment plans, gift cards and multi-use passes.
- **Reserved seating:** A drag-and-drop seating plan builder, a guest seat picker and a box office console.
- **Door management:** QR code tickets, a browser-based door scanner, a live check-in dashboard and Google Wallet passes.
- **Attendee data:** Per-attendee tickets, custom checkout questions, bulk attendee import and CSV export of sales.
- **Sell anywhere:** Embed the ticket form on any website.

### 🤝 Appointments and sync

- **Calendly-style booking:** Appointment types, weekly hours, date overrides, buffers, minimum notice, approvals and optional payment.
- **Two-way sync:** Google Calendar, Outlook / Microsoft 365 and CalDAV (Nextcloud, Radicale, Fastmail).
- **Importing:** .ics and webcal feeds, any page with event JSON-LD, Google Calendar or Eventbrite.

### 🤖 AI (optional, uses your own key)

- **Create events** from pasted text, flyer images or WhatsApp messages.
- **Daily auto-import** of events from a list of URLs (selfhosted installs only).
- **Translate** schedules and events, **scan agendas** into timed parts, and **generate** flyers, descriptions and schedule branding.

### 📈 Audience and marketing

- **Newsletters:** Block editor, templates, segments, A/B testing and delivery stats, sent through your own SMTP with no sending cap on a selfhosted install.
- **Subscribers:** Visitors sign up by email and hear automatically when new events are published. An optional "tell me when tickets go on sale" list per event.
- **Community tools:** Fan photos, videos and comments with moderation, post-event feedback, photo galleries and carpool matching.
- **Promotion:** An event graphics generator for social posts, sponsor logos, and event boosting through Meta Ads.
- **Analytics:** Built-in views, devices, top events and sales tracking.

### 🛠️ Customization and developer tools

- **Look and feel:** Custom CSS, themes, fonts and header layouts, in light and dark mode.
- **REST API:** Events, schedules, sub-schedules and sales, with an [OpenAPI spec](https://eventschedule.com/api/openapi.json).
- **Webhooks:** HMAC-signed POST notifications for sales, events and check-ins.
- **AI agent support:** [Agent workflows](https://eventschedule.com/.well-known/agents.json), [llms.txt](https://eventschedule.com/llms.txt) and [llms-full.txt](https://eventschedule.com/llms-full.txt) for agents and developer tools.

### 🔐 Admin and operations

- **Backup and restore:** Export a schedule's data, optionally with its images, and import it again.
- **Security:** TOTP two-factor authentication with recovery codes, a searchable audit log, and optional Google and Facebook sign-in.
- **Teams:** Several members per schedule, as admins or read-only viewers who can still scan tickets at the door, plus team availability tracking.
- **12 interface languages:** English, Spanish, German, French, Italian, Portuguese, Dutch, Romanian, Estonian, Russian, Hebrew and Arabic, with proper RTL for the last two.
- **Multi-tenant mode:** Optionally [run it as a service](https://eventschedule.com/docs/saas) for others, with your own plans and billing.
- **Federation:** Optionally list your public events on eventschedule.com. Off by default, and every listing links back to the event on your own site.

## Hosted or selfhosted

| | Hosted | Selfhosted |
|---|---|---|
| **Setup** | [Sign up](https://eventschedule.com) and publish in minutes | [Docker, Softaculous or manual install](#installation) |
| **Cost** | Free plan; paid plans add paid ticketing and advanced features ([pricing](https://eventschedule.com/pricing)) | Free, with every feature included |
| **Ticket sales** | No platform fee | No platform fee |
| **Infrastructure** | We run the servers | Your server, your data |
| **Updates** | Automatic | One click in the admin panel |
| **Branding** | Removable on paid plans | A small Event Schedule credit on guest pages (see [License](#license)) |

## Installation

You need PHP 8.2+, MySQL 5.7+ or MariaDB 10.3+, Apache or Nginx, HTTPS and a cron entry. The [Installation Guide](https://eventschedule.com/docs/selfhost/installation) covers every step in detail.

### Docker

```bash
git clone https://github.com/eventschedule/dockerfiles.git
cd dockerfiles
# Set DB_PASSWORD and APP_URL in .env, then:
docker compose up --build -d
```

The app is then at http://localhost:8080. See the [dockerfiles repo](https://github.com/eventschedule/dockerfiles) for the details.

### Softaculous

[One-click install](https://www.softaculous.com/apps/calendars/Event_Schedule) on any host that offers Softaculous.

### Manual

1. Create an empty MySQL database and a user for it.
2. Download `eventschedule.zip` from the [latest release](https://github.com/eventschedule/eventschedule/releases/latest) and extract it. Dependencies and compiled assets are included, so the server needs neither Composer nor Node.
3. Point the web root at the `public` directory, and make `storage`, `bootstrap`, `public` and `.env` writable by the web server.
4. Run `cp .env.example .env`, leave `APP_URL` blank and open the site. The setup wizard tests the database connection, runs the migrations, creates your admin account and writes the config.
5. Add the cron entry that runs scheduled tasks and the queue:

```
* * * * * php /path/to/eventschedule/artisan schedule:run
```

A selfhosted install is single-user by default: the first account is the admin and further sign-ups are closed. Set `ALLOW_REGISTRATION=true` to open them.

## Configuration

Everything optional is switched on by adding your own credentials to `.env`. Until then the feature does not appear and nothing is sent to that service.

| Feature | Variables | Guide |
|---|---|---|
| Email (tickets, reminders, newsletters) | `MAIL_*` | [Email](https://eventschedule.com/docs/selfhost/email) |
| Stripe payments | `STRIPE_PLATFORM_KEY`, `STRIPE_PLATFORM_SECRET` | [Stripe](https://eventschedule.com/docs/selfhost/stripe) |
| PayPal payments | `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` | [`.env.example`](.env.example) |
| Payfast payments | `PAYFAST_MERCHANT_ID`, `PAYFAST_MERCHANT_KEY`, `PAYFAST_PASSPHRASE` | [`.env.example`](.env.example) |
| AI features | `GEMINI_API_KEY` or `OPENAI_API_KEY` | [AI](https://eventschedule.com/docs/selfhost/ai) |
| Google Calendar sync | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | [Google Calendar](https://eventschedule.com/docs/selfhost/google-calendar) |
| Outlook / Microsoft 365 sync | `MICROSOFT_CLIENT_ID`, `MICROSOFT_CLIENT_SECRET` | [Microsoft Calendar](https://eventschedule.com/docs/selfhost/microsoft-calendar) |
| Google Wallet passes | `GOOGLE_WALLET_ISSUER_ID`, `GOOGLE_WALLET_SERVICE_ACCOUNT` | [Google Wallet](https://eventschedule.com/docs/selfhost/google-wallet) |
| Event boosting (Meta Ads) | `META_*` | [Boost](https://eventschedule.com/docs/selfhost/boost) |
| Web push notifications | `ONESIGNAL_APP_ID`, `ONESIGNAL_REST_API_KEY` | [`.env.example`](.env.example) |
| Bot protection (Cloudflare Turnstile) | `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` | [`.env.example`](.env.example) |

Schedule owners can also connect their own PayPal, Payfast or Invoice Ninja account, and their own CalDAV server, from the app's settings. [`.env.example`](.env.example) documents every variable.

## What a fresh install connects to

There is no usage reporting and no license server. Left as installed, the app makes two outbound requests on its own, both plain downloads:

- **GitHub**, once a day, to read the latest release number so the admin panel can show an update badge.
- **DB-IP**, once a month, to fetch the free country-level GeoIP database behind the analytics country breakdown.

Everything else is opt-in:

- Crash reports to the developers are sent only if you tick the box in the setup wizard (`REPORT_ERRORS`).
- Third-party services such as your mail server, Stripe, Google, Microsoft, OneSignal, Cloudflare Turnstile or your AI provider are contacted only after you add their credentials.
- Federation, and sharing translation fixes upstream, happen only when an admin turns them on.
- Embedded YouTube videos and Google maps (maps need your own Google Maps key) are click-to-load. Guests see a preview image served by your install, and their browser contacts Google only after they click it or accept cookies.

## Development

```bash
git clone https://github.com/eventschedule/eventschedule.git
cd eventschedule
composer install
npm install
cp .env.example .env    # set DB_*, APP_URL=http://localhost:8000 and SESSION_SECURE_COOKIE=false
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run dev             # Vite with hot reload; use `npm run build` for production assets
php artisan serve
```

```bash
php artisan test            # Feature and Unit tests, run against a separate eventschedule_test schema
./vendor/bin/pint --dirty   # code style for the files you changed
```

## Tech stack

[PHP](https://php.net) 8.2+ / [Laravel](https://laravel.com) 11 / [Vue.js](https://vuejs.org) 3 / [Tailwind CSS](https://tailwindcss.com) / [MySQL](https://mysql.com) or MariaDB / [Vite](https://vite.dev)

## Documentation

- [User guide](https://eventschedule.com/docs) - Schedules, events, tickets, seating, appointments, newsletters and analytics
- [Selfhost guide](https://eventschedule.com/docs/selfhost) - Installation, the admin panel and every integration in the table above
- [SaaS setup](https://eventschedule.com/docs/saas) - Multi-tenant mode with subdomain routing, custom domains and subscription billing
- [REST API](https://eventschedule.com/docs/developer/api) and [webhooks](https://eventschedule.com/docs/developer/webhooks) - Endpoints, authentication and signature verification

## Contributing

Contributions are welcome! Please open an [issue](https://github.com/eventschedule/eventschedule/issues) to report bugs or suggest features, or submit a pull request. Translation fixes can be made in the admin panel's translation manager and shared upstream from there.

## License

Event Schedule is licensed under the [Attribution Assurance License (AAL)](LICENSE), an [OSI-approved](https://opensource.org/license/aal) BSD-style license with an attribution requirement. In practice, guest pages on a selfhosted install keep a small Event Schedule credit linking back to the project.
