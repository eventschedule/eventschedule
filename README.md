# Event Schedule

Event Schedule is an all-in-one platform to create calendars, sell tickets, manage teams, and streamline event check-ins with QR codes.

* Sample Schedule: [openmicnight.eventschedule.com](https://openmicnight.eventschedule.com)

**Choose your setup**

- [Hosted](https://www.eventschedule.com): Our hosted version is a Software as a Service (SaaS) solution. You're up and running in under 5 minutes, with no need to worry about hosting or server infrastructure.
- [Self-Hosted](https://github.com/eventschedule/eventschedule?tab=readme-ov-file#installation-guide): For those who prefer to manage their own hosting and server infrastructure. This version gives you full control and flexibility.

All Pro features from the hosted app are included in the open-source code. We will be offering a $10 per year white-label license to remove the Event Schedule branding from client-facing parts of the app. 

## Features

- 🗓️ **Event Calendars:** Create and share event calendars effortlessly to keep your audience informed.  
- 🎟️ **Sell Tickets Online:** Offer ticket sales directly through the platform with a seamless checkout process.  
- 💳 **Online Payments with Invoice Ninja Integration:** Accept secure online payments via [Invoice Ninja](https://www.invoiceninja.com).  
- 🔁 **Recurring Events:** Schedule recurring events which occur on a regular basis.  
- 📲 **QR Code Ticketing:** Generate and scan QR codes for easy and secure event check-ins.  
- 💻 **Support for Online Events:** Use the platform to sell tickets to online events.  
- 👥 **Team Scheduling:** Collaborate with team members to manage availability and coordinate event schedules.  
- 🌍 **Multi-Language Support:** Provide a localized experience with support for multiple languages.

<img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/schedule.png?raw=true" width="100%" alt="Schedule Screenshot">

<img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/event.png?raw=true" width="100%" alt="Event Screenshot">

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

---

### 5. Set Up the Cron Job

Add the following line to your crontab to ensure scheduled tasks run automatically:

```bash
* * * * * php /path/to/eventschedule/artisan schedule:run
```

---

You're all set! 🎉 Event Schedule should now be up and running.