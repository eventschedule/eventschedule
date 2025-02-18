# Event Schedule

### Schedule Events. Engage your Audience.

An app for talent, vendors, venues, and curators to set up & and promote their event schedule calendars.

#### Sample Schedule: [openmicnight.eventschedule.com](https://openmicnight.eventschedule.com)

<img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/schedule.png?raw=true" width="100%" alt="Schedule Screenshot">

<img src="https://github.com/eventschedule/eventschedule/blob/main/public/images/screenshots/event.png?raw=true" width="100%" alt="Event Screenshot">

## Steps to install

### Setup the database

```
CREATE DATABASE eventschedule;
CREATE USER 'eventschedule'@'localhost' IDENTIFIED BY 'eventschedule';
GRANT ALL PRIVILEGES ON eventschedule.* TO 'eventschedule'@'localhost';
```

### Setup the application

* Clone the repository: 

```
git clone https://github.com/hillelcoren/eventschedule.git
```

* Setup the `.env` file: 

```
cp .env.example .env
```

* Generate the application key: 

```
php artisan key:generate
```

* Install the dependencies: 

```
composer install
```

* Run the migrations: 

```
php artisan migrate
```

* Build the assets: 

```
npm run dev
```

* Run the development server: 

```
php artisan serve
```

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
