# Security Configuration Guide

## Required Environment Variables

Add these to your `.env` file for optimal security:

```bash
# Session Security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Application Security
APP_DEBUG=false
APP_ENV=production

# Database Security
DB_CONNECTION=mysql
# Use strong database credentials

# Cache Security  
CACHE_DRIVER=redis  # Recommended for production

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# External Services (if using)
SENTRY_LARAVEL_DSN=your_sentry_dsn_here
GEMINI_API_KEY=your_api_key_here
```

## Security Features Implemented

1. **Updated Dependencies**: All packages updated to latest versions with security patches
2. **Security Headers Middleware**: Active globally with improved CSP
3. **Session Encryption**: Enabled by default
4. **Content Security Policy**: Environment-aware with nonce support
5. **Input Validation**: Comprehensive validation throughout the application
6. **File Upload Security**: MIME type and file signature validation
7. **API Rate Limiting**: 60 requests per minute with brute force protection
8. **CSRF Protection**: Enabled for all routes except webhooks
9. **SQL Injection Prevention**: Using Eloquent ORM with parameterized queries
10. **XSS Prevention**: HTML Purifier for markdown content

## Content Security Policy (CSP)

The application uses different CSP configurations for development and production:

### Development Environment
- More permissive to allow Vite dev server and hot reloading
- Allows `unsafe-inline` and `unsafe-eval` for development tools
- Supports local development servers on any port

### Production Environment  
- Strict CSP with nonce support for inline scripts
- Only allows trusted domains for external resources
- Blocks unsafe inline content unless nonce is used

### Trusted External Domains

The CSP allows these external domains for legitimate resources:

**Scripts:**
- `*.googleapis.com` - Google services
- `*.gstatic.com` - Google static content
- `*.googletagmanager.com` - Google Tag Manager
- `*.stripe.com` - Stripe payment processing
- `unpkg.com` - CDN for JavaScript libraries (Vue.js, etc.)

**Styles:**
- `*.googleapis.com` - Google Fonts CSS
- `*.gstatic.com` - Google static content  
- `rsms.me` - Inter font CSS

**Fonts:**
- `*.googleapis.com` - Google Fonts
- `*.gstatic.com` - Google Fonts files
- `rsms.me` - Inter font files
- `data:` - Inline font data (base64 encoded fonts)

**Images:**
- `*.googleapis.com` - Google services
- `*.gstatic.com` - Google static content
- `*.stripe.com` - Stripe assets
- `data:` - Inline images (base64 encoded)

### Using CSP Nonces in Templates

For inline scripts in production, use the CSP nonce helper:

```blade
{{-- Using the helper function --}}
<script {!! nonce_attr() !!}>
    console.log('This script will work with CSP');
</script>

{{-- Or get the nonce directly --}}
<script nonce="{{ csp_nonce() }}">
    // Your script here
</script>
```

## Additional Security Recommendations

1. Use HTTPS in production
2. Set strong `APP_KEY` (32 characters)
3. Use Redis for sessions in production
4. Enable Sentry for error monitoring
5. Regular dependency updates with `composer audit`
6. Implement backup strategy for database
7. Use environment-specific configuration files
8. Monitor server logs for suspicious activity

## Security Headers Applied

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` (HTTPS only)
- Content Security Policy with environment-aware configuration

## Troubleshooting CSP Issues

If you see CSP errors in the browser console:

1. **Check Environment**: CSP is more permissive in local development
2. **Use Nonces**: For inline scripts in production, use the `nonce_attr()` helper
3. **Whitelist Domains**: Add trusted domains to the SecurityHeaders middleware
4. **Check Debug Toolbar**: CSP is disabled for `_debugbar/*` routes

### Adding New Trusted Domains

If you need to add a new external domain, edit `app/Http/Middleware/SecurityHeaders.php`:

```php
// For development and production
"script-src 'self' ... your-trusted-domain.com",
"style-src 'self' ... your-trusted-domain.com", 
"font-src 'self' ... your-trusted-domain.com",
"img-src 'self' ... your-trusted-domain.com"
```

**Security Note**: Only add domains you trust completely, as they can execute scripts on your site.

## Regular Security Maintenance

Run these commands regularly:

```bash
# Check for security vulnerabilities
composer audit

# Update dependencies
composer update

# Clear and optimize cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize
``` 