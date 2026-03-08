<div align="center">
    <picture>
        <source srcset="public/images/dark_logo.png" media="(prefers-color-scheme: light)">
        <img src="public/images/light_logo.png" alt="Event Schedule Logo" width="350" media="(prefers-color-scheme: dark)">
    </picture>
    <p>
        An open-source platform to share events, sell tickets and bring communities together.
    </p>
    <p>
        <img src="https://img.shields.io/badge/license-AAL-blue" alt="License: AAL">
        <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
        <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" alt="Laravel 11">
    </p>
    <p>
        <a href="https://eventschedule.com">Website</a> &middot;
        <a href="https://eventschedule.com/docs">Docs</a> &middot;
        <a href="https://github.com/eventschedule/eventschedule/issues">Issues</a>
    </p>
</div>

## Screenshots

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_1.png?raw=true" width="49%" alt="Guest > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_2.png?raw=true" width="49%" alt="Guest > Event">
</div>

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_3.png?raw=true" width="49%" alt="Admin > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_4.png?raw=true" width="49%" alt="Admin > Event">
</div>

## Why Event Schedule?

- **Free and open source** - Unlimited events and schedules. Selfhost on your own server or use the hosted version at [eventschedule.com](https://eventschedule.com).
- **No platform fees** - Accept payments directly via Stripe. You keep 100% of ticket revenue (minus Stripe's processing fee).
- **AI-powered** - Create events from text, images, or WhatsApp messages. Generate flyers, translate your schedule, and more.
- **All-in-one** - Ticketing, QR check-ins, newsletters, analytics, Google Calendar sync, and a REST API, all built in.

## Features

- **Event calendars** with recurring events, sub-schedules, and search
- **Ticket sales** with multiple ticket types, QR code check-ins, promo codes, and waitlists
- **Google Calendar and CalDAV sync** with bidirectional real-time updates
- **AI event parsing** from text, images, flyers, or WhatsApp messages
- **Newsletter system** with drag-and-drop editor, templates, A/B testing, and analytics
- **Built-in analytics** with page views, device breakdown, and top events
- **REST API and webhooks** for programmatic access and integrations
- **Fully customizable** with custom CSS, themes, white-label branding, and custom domains

<details>
<summary>See all features</summary>

### Event Management
- 🗓️ **Event Calendars:** Create and share event calendars effortlessly to keep your audience informed.
- 🔁 **Recurring Events:** Schedule recurring events which occur on a regular basis.
- 📋 **Sub-schedules:** Organize events into multiple sub-schedules for better categorization and management.
- 🔍 **Search:** Powerful search functionality to help users find specific events or content across your schedule.
- 📥 **Guest Event Submissions:** Allow community members to propose events directly to your schedule for review.
- 📋 **Event Cloning:** Duplicate events with all ticket configurations preserved.
- 📊 **Event Polls:** Add multiple choice polls to events. Guests vote and see real-time results.
- 🔒 **Private Events:** Password-protect events or hide them from the public schedule for invite-only gatherings.
- 📑 **Event Agenda:** Break events into timed segments with names, descriptions, and individual start and end times.
- 📊 **Configurable Dashboard:** Customize which panels appear on your admin dashboard.
- 💾 **Backup & Restore:** Export and import schedule data with optional images.
- 📝 **Free Event Registration:** Native sign-up for free events with optional capacity limits.

### Ticketing & Payments
- 🎟️ **Sell Tickets Online:** Offer ticket sales directly through the platform with a seamless checkout process.
- 🎫 **Multiple Ticket Types:** Offer different ticket tiers, such as Standard or VIP, to meet various audience needs.
- 🔢 **Ticket Quantity Limits:** Set a maximum number of tickets available for each event to manage capacity.
- ⏳ **Ticket Reservations:** Allow attendees to reserve tickets with a configurable release time before purchase.
- 📲 **QR Code Ticketing:** Generate and scan QR codes for easy and secure event check-ins.
- 💻 **Online Events:** Use the platform to sell tickets to online events.
- 💳 **Online Payments:** Accept secure online payments via [Stripe](https://stripe.com), [Invoice Ninja](https://www.invoiceninja.com), or payment links.
- 🏷️ **Promo Codes:** Create discount codes with percentage or fixed amounts, usage limits, and ticket-specific targeting.
- 🎫 **Individual Tickets:** Collect per-attendee details with individual confirmation emails and QR codes.
- 📊 **Check-in Dashboard:** Track real-time attendance with per-ticket breakdown.
- ⏳ **Ticket Waitlist:** Automatically notify attendees when sold-out tickets become available.
- 🖥️ **Embed Ticket Widget:** Embed a ticket purchase or RSVP form on external websites.
- 📤 **Sales CSV Export:** Export sales data including custom fields.

### Integrations
- 📅 **Calendar Integration:** Enable attendees to add events directly to Google, Apple, or Microsoft calendars.
- 🔄 **Google Calendar Sync:** Automatically sync events between Event Schedule and Google Calendar, with real-time updates via webhooks.
- 📅 **CalDAV Sync:** Sync events with any CalDAV-compatible calendar server including Nextcloud, Radicale, and Fastmail.
- 🔗 **Third-Party Event Import:** Automatically import events from third-party websites to expand your calendar offerings.
- 🖥️ **Website Embedding:** Embed your schedule on any website using a simple iframe widget.
- 🔐 **Social Login:** Sign in quickly using Google or Facebook accounts.
- 🎪 **Eventbrite Import:** Import events from Eventbrite into your schedule.
- 🔔 **Webhooks:** Receive POST notifications for sales, events, and check-ins.
- 💬 **WhatsApp Event Creation:** Create events by sending messages or images via WhatsApp with AI parsing.

### AI-Powered
- 🤖 **AI Event Parsing:** Automatically extract event details using AI to quickly create new events.
- 🤖 **AI Translation:** Automatically translate your entire schedule into multiple languages using AI.
- 🤖 **AI Flyer Generation:** Generate event flyer images from event details.
- 🎨 **AI Style Generation:** Generate cohesive schedule branding including images, accent color, and font.
- 📑 **AI Agenda Scanning:** Scan agendas to automatically create event parts.

### Collaboration & Marketing
- 👥 **Team Scheduling:** Collaborate with team members to manage availability and coordinate event schedules.
- 🎨 **Event Graphics Generator:** Create beautiful graphics of your upcoming events with flyers, QR codes, and event details for social media and marketing.
- 📊 **Built-in Analytics:** Track page views with an integrated analytics dashboard showing views over time, device breakdown, and top events.
- 👁️ **Follow Schedules:** Users can follow schedules to stay updated on new events.
- 📧 **Email Notifications:** Automatic ticket confirmation emails sent to buyers.
- 📧 **Newsletters:** Send newsletters to followers and ticket buyers with a drag-and-drop block editor, five professional templates, audience segments, A/B testing, and real-time delivery analytics.
- 📸 **Fan Videos & Comments:** Let attendees submit videos, photos, and comments on events with built-in moderation.
- ⭐ **Post-Event Feedback:** Collect star ratings and comments from attendees after events.
- 🏢 **Sponsor Logos:** Display sponsor and partner logos with tiers on your schedule page.
- 🔔 **Sale Notifications:** Receive email alerts when tickets are sold.
- 📢 **Event Boosting:** Promote events with Meta Ads integration.

### Customization
- 📝 **Custom Fields:** Collect additional information from ticket buyers with customizable form fields at both event and ticket levels. Supports text, dropdown, date, and yes/no field types.
- 🎨 **Custom CSS Styling:** Personalize your schedule's appearance with custom CSS to match your brand identity.
- 🌐 **Multi-Language Interface:** App available in 11 languages (English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, Russian).
- 🎨 **Profile Themes:** Customize header images, background gradients, and fonts.
- 🏷️ **White-label Branding:** Remove Event Schedule branding for a fully branded experience.
- 🌐 **Custom Domains:** Use your own domain name for your schedule with automatic SSL support.
- 📍 **Venue Location Maps:** Show event venues on Google Maps.
- 📅 **iCal Download:** Download .ics files for individual events and recurring event dates.

### Developer Tools
- 🤖 **AI Agent Support:** Manage events programmatically with [AI agent workflows](https://eventschedule.com/.well-known/agents.json), an [OpenAPI 3.0 spec](https://eventschedule.com/api/openapi.json), [llms.txt](https://eventschedule.com/llms.txt), and [llms-full.txt](https://eventschedule.com/llms-full.txt) for seamless integration with AI agents and developer tools.
- 🔌 **REST API:** Access and manage your events programmatically through a REST API.
- 🚀 **Automatic App Updates:** Keep the platform up to date effortlessly with one-click automatic updates.

</details>

## Getting Started

| | Hosted | Selfhosted |
|---|---|---|
| **Setup** | [Up and running in under 5 minutes](https://www.eventschedule.com) | [Full control over your infrastructure](https://eventschedule.com/docs/installation) |
| **Infrastructure** | We handle hosting and servers | You manage your own servers |
| **Updates** | Automatic | One-click updates |

> [!NOTE]
> You can use [Softaculous](https://www.softaculous.com/apps/calendars/Event_Schedule) to automatically install the selfhosted app.

## Installation

For detailed installation instructions, see the [Installation Guide](https://eventschedule.com/docs/installation).

Quick start options:
- **[Softaculous](https://www.softaculous.com/apps/calendars/Event_Schedule)**: One-click automated installation
- **[Docker](https://github.com/eventschedule/dockerfiles)**: Containerized deployment
- **[Manual Installation](https://eventschedule.com/docs/installation)**: Step-by-step guide

## Tech Stack

[PHP](https://php.net) 8.2+ / [Laravel](https://laravel.com) 11 / [Vue.js](https://vuejs.org) 3 / [Tailwind CSS](https://tailwindcss.com) / [MySQL](https://mysql.com) / [Vite](https://vite.dev)

## Documentation

- [SaaS Setup](https://eventschedule.com/docs/saas) - Configure Event Schedule for multi-tenant SaaS deployment with subdomain routing
- [Stripe Setup](https://eventschedule.com/docs/stripe) - Set up Stripe Connect for ticket sales and Cashier for subscription billing
- [Google Calendar Setup](https://eventschedule.com/docs/google-calendar) - Enable bidirectional sync with Google Calendar

## Contributing

Contributions are welcome! Please open an [issue](https://github.com/eventschedule/eventschedule/issues) to report bugs or suggest features, or submit a pull request.

## License

Event Schedule is licensed under the [Attribution Assurance License (AAL)](LICENSE).
