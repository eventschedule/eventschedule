<div align="center">
    <picture>
        <source srcset="public/images/dark_logo.png" media="(prefers-color-scheme: light)">
        <img src="public/images/light_logo.png" alt="Event Schedule Logo" width="350" media="(prefers-color-scheme: dark)">
    </picture>
    <p>
        An open-source platform to share events, sell tickets and bring communities together.
    </p>
</div>

---

## Getting Started

| | Hosted | Self-Hosted |
|---|---|---|
| **Setup** | [Up and running in under 5 minutes](https://www.eventschedule.com) | [Full control over your infrastructure](https://eventschedule.com/docs/installation) |
| **Infrastructure** | We handle hosting and servers | You manage your own servers |
| **Updates** | Automatic | One-click updates |

> [!NOTE]
> You can use [Softaculous](https://www.softaculous.com/apps/calendars/Event_Schedule) to automatically install the self-hosted app.

## Screenshots

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_1.png?raw=true" width="49%" alt="Guest > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_2.png?raw=true" width="49%" alt="Guest > Event">
</div>

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_3.png?raw=true" width="49%" alt="Admin > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_4.png?raw=true" width="49%" alt="Admin > Event">
</div>

## Features

### Event Management
- 🗓️ **Event Calendars:** Create and share event calendars effortlessly to keep your audience informed.
- 🔁 **Recurring Events:** Schedule recurring events which occur on a regular basis.
- 📋 **Sub-schedules:** Organize events into multiple sub-schedules for better categorization and management.
- 🔍 **Search:** Powerful search functionality to help users find specific events or content across your schedule.
- 📥 **Guest Event Submissions:** Allow community members to propose events directly to your schedule for review.
- 📋 **Event Cloning:** Duplicate events with all ticket configurations preserved.

### Ticketing & Payments
- 🎟️ **Sell Tickets Online:** Offer ticket sales directly through the platform with a seamless checkout process.
- 🎫 **Multiple Ticket Types:** Offer different ticket tiers, such as Standard or VIP, to meet various audience needs.
- 🔢 **Ticket Quantity Limits:** Set a maximum number of tickets available for each event to manage capacity.
- ⏳ **Ticket Reservations:** Allow attendees to reserve tickets with a configurable release time before purchase.
- 📲 **QR Code Ticketing:** Generate and scan QR codes for easy and secure event check-ins.
- 💻 **Online Events:** Use the platform to sell tickets to online events.
- 💳 **Online Payments:** Accept secure online payments via [Stripe](https://stripe.com), [Invoice Ninja](https://www.invoiceninja.com), or payment links.

### Integrations
- 📅 **Calendar Integration:** Enable attendees to add events directly to Google, Apple, or Microsoft calendars.
- 🔄 **Google Calendar Sync:** Automatically sync events between Event Schedule and Google Calendar, with real-time updates via webhooks.
- 📅 **CalDAV Sync:** Sync events with any CalDAV-compatible calendar server including Nextcloud, Radicale, and Fastmail.
- 🔗 **Third-Party Event Import:** Automatically import events from third-party websites to expand your calendar offerings.
- 🖥️ **Website Embedding:** Embed your schedule on any website using a simple iframe widget.
- 🔐 **Social Login:** Sign in quickly using Google or Facebook accounts.

### AI-Powered
- 🤖 **AI Event Parsing:** Automatically extract event details using AI to quickly create new events.
- 🤖 **AI Translation:** Automatically translate your entire schedule into multiple languages using AI.

### Collaboration & Marketing
- 👥 **Team Scheduling:** Collaborate with team members to manage availability and coordinate event schedules.
- 🎨 **Event Graphics Generator:** Create beautiful graphics of your upcoming events with flyers, QR codes, and event details for social media and marketing.
- 📊 **Built-in Analytics:** Track page views with an integrated analytics dashboard showing views over time, device breakdown, and top events—no external services required.
- 👁️ **Follow Schedules:** Users can follow schedules to stay updated on new events.
- 📧 **Email Notifications:** Automatic ticket confirmation emails sent to buyers.
- 📧 **Newsletters:** Send newsletters to followers and ticket buyers with a drag-and-drop block editor, five professional templates, audience segments, A/B testing, and real-time delivery analytics.

### Customization
- 📝 **Custom Fields:** Collect additional information from ticket buyers with customizable form fields at both event and ticket levels. Supports text, dropdown, date, and yes/no field types.
- 🎨 **Custom CSS Styling:** Personalize your schedule's appearance with custom CSS to match your brand identity.
- 🌐 **Multi-Language Interface:** App available in 11 languages (English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, Russian).
- 🎨 **Profile Themes:** Customize header images, background gradients, and fonts.

### Developer Tools
- 🤖 **AI Agent Support:** Manage events programmatically with [AI agent workflows](https://eventschedule.com/.well-known/agents.json), an [OpenAPI 3.0 spec](https://eventschedule.com/api/openapi.json), and [llms.txt](https://eventschedule.com/llms.txt) for seamless integration with AI agents and developer tools.
- 🔌 **REST API:** Access and manage your events programmatically through a REST API.
- 🚀 **Automatic App Updates:** Keep the platform up to date effortlessly with one-click automatic updates.

## Installation Guide

For detailed installation instructions, see the [Installation Guide](https://eventschedule.com/docs/installation).

Quick start options:
- **[Softaculous](https://www.softaculous.com/apps/calendars/Event_Schedule)**: One-click automated installation
- **[Docker](https://github.com/eventschedule/dockerfiles)**: Containerized deployment
- **[Manual Installation](https://eventschedule.com/docs/installation)**: Step-by-step guide

## Documentation

For advanced configuration, see the following guides:

- [SaaS Setup](https://eventschedule.com/docs/saas) - Configure Event Schedule for multi-tenant SaaS deployment with subdomain routing
- [Stripe Setup](https://eventschedule.com/docs/stripe) - Set up Stripe Connect for ticket sales and Cashier for subscription billing
- [Google Calendar Setup](https://eventschedule.com/docs/google-calendar) - Enable bidirectional sync with Google Calendar

## Testing

Event Schedule includes a comprehensive browser test suite powered by Laravel Dusk.

> [!WARNING]  
> WARNING: Running the tests will empty the database. 

### Prerequisites

1. **Install Laravel Dusk:**
```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

2. **Configure Chrome Driver:**
```bash
php artisan dusk:chrome-driver
```

3. **Set up test environment:**
```bash
cp .env .env.dusk.local
# Configure your test database in .env.dusk.local
```

### Running Tests

```bash
# Run all browser tests
php artisan dusk
```