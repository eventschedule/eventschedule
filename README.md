# Event Schedule

### Schedule Events. Engage your Audience.

An app for talent, vendors, venues, and curators to set up & and promote their event schedule calendars.

## Steps to install

### Setup the database

```
CREATE DATABASE eventschedule;
CREATE USER 'eventschedule'@'localhost' IDENTIFIED BY 'eventschedule';
GRANT ALL PRIVILEGES ON eventschedule.* TO 'eventschedule'@'localhost';
```

### Setup the application

1. Clone the repository
2. Run `composer install`
3. Run `php artisan migrate`
4. Run `php artisan serve`
5. Run `npm run dev`

#### File permissions

```
cd /path/to/eventschedule/code
chmod -R 755 storage
sudo chown -R www-data:www-data storage bootstrap public
```

#### Setup the cron job

```
* * * * * php /path/to/eventschedule/artisan schedule:run
```
