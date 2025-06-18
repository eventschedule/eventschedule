<div align="center">
    <picture>
        <source srcset="public/images/dark_logo.png" media="(prefers-color-scheme: light)">
        <img src="public/images/light_logo.png" alt="Event Schedule Logo" width="350" media="(prefers-color-scheme: dark)">
    </picture>
</div>

---

<p>
    An open-source platform to create calendars, sell tickets and streamline event check-ins with QR codes
</p>

* Sample Schedule: [openmicnight.eventschedule.com](https://openmicnight.eventschedule.com)

**Choose your setup**

- [Hosted](https://www.eventschedule.com): Our hosted version is a Software as a Service (SaaS) solution. You're up and running in under 5 minutes, with no need to worry about hosting or server infrastructure.
- [Self-Hosted](https://github.com/eventschedule/eventschedule?tab=readme-ov-file#installation-guide): For those who prefer to manage their own hosting and server infrastructure. This version gives you full control and flexibility.

## Features

- 🗓️ **Event Calendars:** Create and share event calendars effortlessly to keep your audience informed.  
- 🎟️ **Sell Tickets Online:** Offer ticket sales directly through the platform with a seamless checkout process.  
- 💳 **Online Payments with Invoice Ninja Integration:** Accept secure online payments via [Invoice Ninja](https://www.invoiceninja.com).  
- 🤖 **AI Event Parsing:** Automatically extract event details using AI to quickly create new events.
- 🔁 **Recurring Events:** Schedule recurring events which occur on a regular basis.  
- 📲 **QR Code Ticketing:** Generate and scan QR codes for easy and secure event check-ins.  
- 💻 **Support for Online Events:** Use the platform to sell tickets to online events.  
- 👥 **Team Scheduling:** Collaborate with team members to manage availability and coordinate event schedules.  
- 🌍 **Multi-Language Support:** Provide a localized experience with support for multiple languages.
- 🤖 **AI Translation:** Automatically translate your entire schedule into multiple languages using AI.
- 🎫 **Multiple Ticket Types:** Offer different ticket tiers, such as Standard or VIP, to meet various audience needs.  
- 🔢 **Ticket Quantity Limits:** Set a maximum number of tickets available for each event to manage capacity.  
- ⏳ **Ticket Reservation System:** Allow attendees to reserve tickets with a configurable release time before purchase.  
- 📅 **Calendar Integration:** Enable attendees to add events directly to Google, Apple, or Microsoft calendars. 
- 🔌 **REST API:** Access and manage your events programmatically through a REST API.
- 🚀 **Automatic App Updates:** Keep the platform up to date effortlessly with one-click automatic updates.  

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_1.png?raw=true" width="49%" alt="Guest > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_2.png?raw=true" width="49%" alt="Guest > Event">
</div>

<div style="display: flex; gap: 10px;">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_3.png?raw=true" width="49%" alt="Admin > Schedule">
    <img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/screen_4.png?raw=true" width="49%" alt="Admin > Event">
</div>

## Installation Guide

Follow these steps to set up Event Schedule:

### 1. Set Up the Database

Run the following commands to create the MySQL database and user:

```sql
CREATE DATABASE eventschedule;
CREATE USER 'eventschedule'@'localhost' IDENTIFIED BY 'change_me';
GRANT ALL PRIVILEGES ON eventschedule.* TO 'eventschedule'@'localhost';
```

---

### 2. Set Up the Application

Copy [eventschedule.zip](https://github.com/eventschedule/eventschedule/releases/latest) to your server and unzip it.

---

### 3. Set File Permissions

Ensure correct permissions for storage and cache directories:

```bash
cd /path/to/eventschedule
chmod -R 755 storage
sudo chown -R www-data:www-data storage bootstrap public
```

---

### 4. Set Up the Application

Copy the `.env.example` file to `.env` and then access the application at `https://your-domain.com`.

```bash
cp .env.example .env
```

<img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/setup.png?raw=true" width="100%" alt="Setup"/>

---

### 5. Set Up the Cron Job

Add the following line to your crontab to ensure scheduled tasks run automatically:

```bash
* * * * * php /path/to/eventschedule/artisan schedule:run
```

---

You're all set! 🎉 Event Schedule should now be up and running.

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