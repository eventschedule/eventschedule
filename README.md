# Event Schedule

### Schedule Events. Engage your Audience.

An app for talent, vendors, venues, and curators to set up & and promote their event schedule calendars.

* Sample Schedule: [openmicnight.eventschedule.com](https://openmicnight.eventschedule.com)

## Key Features

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

Fill in the form with your details.

---

### 5. Set Up the Cron Job

Add the following line to your crontab to ensure scheduled tasks run automatically:

```bash
* * * * * php /path/to/eventschedule/artisan schedule:run
```

---

You're all set! 🎉 Event Schedule should now be up and running.

## Developer Setup

#### Clone the Repository

```bash
git clone https://github.com/eventschedule/eventschedule.git
cd eventschedule
```

#### Configure Environment Variables

```bash
cp .env.example .env
```

Edit the `.env` file to match your database and application settings as needed.

#### Generate Application Key

```bash
php artisan key:generate
```

#### Install PHP Dependencies

```bash
composer install
```

#### Run Database Migrations

```bash
php artisan migrate
```

#### Build Frontend Assets

```bash
npm install
npm run dev
```

#### Start the Development Server

```bash
php artisan serve
```