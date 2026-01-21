# SaaS Installation Setup

This document explains how to configure Event Schedule for SaaS (Software as a Service) deployment, where you host the platform for multiple customers using subdomains.

## Overview

Event Schedule supports two deployment modes:

| Mode | Routing | Use Case |
|------|---------|----------|
| **Self-hosted** | Path-based (`/schedule-name/...`) | Single organization or personal use |
| **SaaS/Hosted** | Subdomain-based (`schedule-name.yourdomain.com`) | Multi-tenant platform for multiple customers |

In SaaS mode, each customer (schedule/role) gets their own subdomain, and the main domain can display marketing pages to attract new signups.

## Prerequisites

1. Completed base installation of Event Schedule
2. A domain name with DNS access
3. Ability to configure wildcard SSL certificates
4. Web server configured to handle wildcard subdomains (Apache or Nginx)

## Environment Configuration

Add the following variables to your `.env` file to enable SaaS mode:

### Core SaaS Settings

```env
# Enable SaaS mode with subdomain routing
IS_HOSTED=true

# Enable marketing pages at the main domain
IS_NEXUS=true

# Your application name (shown in emails and alt text)
APP_NAME=Your Platform Name

# Main application URL
APP_URL=https://yourdomain.com

# Marketing site URL (can be same as APP_URL)
APP_MARKETING_URL=https://yourdomain.com
```

| Variable | Default | Description |
|----------|---------|-------------|
| `IS_HOSTED` | `false` | Enable subdomain-based routing for multi-tenant SaaS |
| `IS_NEXUS` | `false` | Display marketing pages at the main domain |
| `APP_NAME` | `Laravel` | Brand name shown in emails, page titles, and image alt text |
| `APP_URL` | - | Primary application URL |
| `APP_MARKETING_URL` | `https://eventschedule.com` | URL for marketing site links |

### Branding Customization

```env
# Logo for light backgrounds (header, emails)
APP_LOGO_DARK=/images/dark_logo.png

# Logo for dark backgrounds (dark mode, footers)
APP_LOGO_LIGHT=/images/light_logo.png
```

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_LOGO_DARK` | `/images/dark_logo.png` | Logo displayed on light backgrounds |
| `APP_LOGO_LIGHT` | `/images/light_logo.png` | Logo displayed on dark backgrounds |

**Logo Guidelines:**
- Place logo files in `public/images/`
- Recommended dimensions: 200px width, transparent background
- Supported formats: PNG, SVG
- The dark logo should have dark/black text (for light backgrounds)
- The light logo should have light/white text (for dark backgrounds)

### Pricing and Trial Configuration

```env
# Free trial length in days for new Pro subscribers
TRIAL_DAYS=365
```

| Variable | Default | Description |
|----------|---------|-------------|
| `TRIAL_DAYS` | `365` | Number of days for free trial when new schedules are created or users subscribe to Pro |

**How Trials Work:**
- When a new schedule is created in hosted mode, it gets Pro features for the configured trial period
- When users subscribe via Stripe, eligible users get a trial period before billing starts
- Prices are defined in your Stripe dashboard; the Price IDs are referenced via environment variables

## DNS Configuration

For SaaS mode to work, you need to configure wildcard DNS records.

### DNS Records

Add the following DNS records to your domain:

```
# A record for main domain
yourdomain.com.    A    YOUR_SERVER_IP

# Wildcard A record for subdomains
*.yourdomain.com.  A    YOUR_SERVER_IP
```

Or if using a CNAME:

```
# CNAME for main domain
yourdomain.com.    CNAME    your-server.hosting.com.

# Wildcard CNAME for subdomains
*.yourdomain.com.  CNAME    your-server.hosting.com.
```

### SSL Certificate

You'll need a wildcard SSL certificate that covers both the main domain and all subdomains:

- Certificate should cover: `yourdomain.com` and `*.yourdomain.com`
- Let's Encrypt supports wildcard certificates via DNS-01 challenge
- Many hosting providers offer wildcard certificates

## Web Server Configuration

### Nginx Example

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com *.yourdomain.com;

    ssl_certificate /path/to/wildcard.crt;
    ssl_certificate_key /path/to/wildcard.key;

    root /var/www/eventschedule/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache Example

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com

    DocumentRoot /var/www/eventschedule/public

    SSLEngine on
    SSLCertificateFile /path/to/wildcard.crt
    SSLCertificateKeyFile /path/to/wildcard.key

    <Directory /var/www/eventschedule/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Stripe Subscription Setup (Pro Plans)

To enable paid Pro plans for your customers, configure Stripe subscription billing.

See [STRIPE_SETUP.md](STRIPE_SETUP.md) for detailed Stripe configuration instructions.

### Required Environment Variables

```env
# Stripe Platform (for subscription billing)
STRIPE_PLATFORM_KEY=pk_live_your_publishable_key
STRIPE_PLATFORM_SECRET=sk_live_your_secret_key
STRIPE_PLATFORM_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_PRICE_MONTHLY=price_monthly_price_id
STRIPE_PRICE_YEARLY=price_yearly_price_id
```

### How Subscriptions Work

1. Customers create a schedule (gets a free plan by default)
2. Customers can upgrade to Pro from their schedule's admin page
3. Pro features are unlocked for that schedule
4. Subscriptions are managed per-schedule, not per-user

## Complete Example Configuration

Here's a complete `.env` configuration for a SaaS deployment:

```env
# Application
APP_NAME=My Events Platform
APP_ENV=production
APP_DEBUG=false
APP_URL=https://myevents.com
APP_MARKETING_URL=https://myevents.com

# SaaS Mode
IS_HOSTED=true
IS_NEXUS=true

# Branding
APP_LOGO_DARK=/images/dark_logo.png
APP_LOGO_LIGHT=/images/light_logo.png

# Trial Configuration
TRIAL_DAYS=365

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventschedule
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Session (important for subdomains)
SESSION_DRIVER=database
SESSION_DOMAIN=.myevents.com

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mail_user
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=hello@myevents.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe Platform (optional, for Pro subscriptions)
STRIPE_PLATFORM_KEY=pk_live_...
STRIPE_PLATFORM_SECRET=sk_live_...
STRIPE_PLATFORM_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_MONTHLY=price_...
STRIPE_PRICE_YEARLY=price_...
```

**Important:** Set `SESSION_DOMAIN` to `.yourdomain.com` (with leading dot) to allow session sharing across subdomains.

## Verification Steps

After completing the configuration, verify your setup:

### 1. Test Main Domain

Visit `https://yourdomain.com` - you should see the marketing/landing page if `IS_NEXUS=true`.

### 2. Test Subdomain Routing

1. Create a new account and schedule
2. Note the schedule's subdomain (e.g., `my-schedule`)
3. Visit `https://my-schedule.yourdomain.com`
4. The schedule's public page should load

### 3. Test SSL Certificate

Verify SSL works for both:
- Main domain: `https://yourdomain.com`
- Any subdomain: `https://test.yourdomain.com`

### 4. Test Subscription Flow (if configured)

1. Go to a schedule's admin page
2. Click "Upgrade to Pro"
3. Complete checkout with a test card (`4242 4242 4242 4242`)
4. Verify Pro features are enabled

## Troubleshooting

### Common Issues

1. **Subdomains show 404 or wrong page**
   - Check that `IS_HOSTED=true` is set
   - Verify wildcard DNS is configured correctly
   - Ensure web server is configured for wildcard subdomains

2. **"Session domain mismatch" or login issues across subdomains**
   - Set `SESSION_DOMAIN=.yourdomain.com` (with leading dot)
   - Clear browser cookies and try again

3. **SSL certificate errors on subdomains**
   - Verify wildcard certificate covers `*.yourdomain.com`
   - Check certificate is properly installed in web server

4. **Marketing pages not showing**
   - Verify `IS_NEXUS=true` is set
   - Clear application cache: `php artisan cache:clear`

5. **Logo not displaying**
   - Verify logo files exist in `public/images/`
   - Check file permissions are readable
   - Ensure paths in `.env` match actual file locations

### Logs

Check the application logs for errors:

```bash
tail -f storage/logs/laravel.log
```

## Security Considerations

1. **Environment File**: Never expose `.env` file publicly
2. **HTTPS Required**: Always use HTTPS in production
3. **API Keys**: Keep all API keys and secrets secure
4. **Database**: Use strong database passwords and restrict access
5. **File Permissions**: Ensure proper file permissions on the server
