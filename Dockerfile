# =========================
# Stage: app (php-fpm + code)
# =========================
FROM php:8.3-fpm-alpine AS app

# System deps
RUN apk add --no-cache \
    bash git curl ca-certificates busybox-extras \
    libpng-dev libjpeg-turbo-dev freetype-dev zlib-dev \
    oniguruma-dev libxml2-dev icu-dev \
    libzip-dev zip unzip \
    nodejs npm

# PHP extensions (incl. gd w/ jpeg + freetype)
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install \
    pdo pdo_mysql mbstring exif pcntl bcmath intl opcache zip gd

# Raise PHP upload limits to accommodate large files
RUN { \
      echo 'upload_max_filesize=500M'; \
      echo 'post_max_size=500M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App source (copy local repo instead of cloning)
WORKDIR /var/www/html
COPY . /var/www/html

# Replace AppServiceProvider with a version compatible with non-interactive builds
COPY patches/AppServiceProvider.php /tmp/AppServiceProvider.php
RUN cp /tmp/AppServiceProvider.php /var/www/html/app/Providers/AppServiceProvider.php \
 && rm /tmp/AppServiceProvider.php

# Ensure .env exists BEFORE composer (artisan post-scripts expect it)
RUN [ -f .env ] || cp .env.example .env

# During image build we do not have a database available. Swap to a
# temporary SQLite configuration so that artisan commands executed as part
# of composer scripts do not attempt to connect to MySQL. The original
# configuration is restored immediately after composer install completes.
RUN cp .env .env.dockerbuild \
 && php -r '$path=".env"; $env=file_get_contents($path); $env=preg_replace("/^DB_CONNECTION=.*/m", "DB_CONNECTION=sqlite", $env, 1, $c1); if(!$c1){$env.="\nDB_CONNECTION=sqlite";} $env=preg_replace("/^DB_DATABASE=.*/m", "DB_DATABASE=database/database.sqlite", $env, 1, $c2); if(!$c2){$env.="\nDB_DATABASE=database/database.sqlite";} file_put_contents($path, $env);' \
 && mkdir -p database \
 && touch database/database.sqlite

# Enable public registration in common stacks (no route edits here)
RUN if [ -f config/fortify.php ]; then \
  php -r '$f=\"config/fortify.php\";$s=file_get_contents($f);if(strpos($s,\"Features::registration()\")===false){$s=preg_replace(\"/(\\'features\\'\\s*=>\\s*\\[)/\",\"$1\\n        Laravel\\\\Fortify\\\\Features::registration(),\",$s,1);} file_put_contents($f,$s);'; \
fi
RUN if [ -f config/jetstream.php ]; then \
  php -r '$f=\"config/jetstream.php\";$s=file_get_contents($f);if(strpos($s,\"Features::registration()\")===false){$s=preg_replace(\"/(\\'features\\'\\s*=>\\s*\\[)/\",\"$1\\n        Laravel\\\\Jetstream\\\\Features::registration(),\",$s,1);} file_put_contents($f,$s);'; \
fi
RUN if [ -f routes/web.php ]; then \
  php -r '$f="routes/web.php"; $s=file_get_contents($f); $s=preg_replace("/Auth::routes\\(([^;]*'"'"'register'"'"'\\s*=>\\s*)false/", "Auth::routes($1true", $s, -1, $c); if ($c) file_put_contents($f, $s);'; \
fi

# --- SIGN-UP OVERRIDE: copy file and require it at end of routes/web.php ---
COPY routes_override/_sign_up_override.php /var/www/html/routes/_sign_up_override.php
RUN printf "\n// Allow public sign up without redirecting to /login\nrequire __DIR__.'/_sign_up_override.php';\n" >> routes/web.php

# Gate any forceScheme('https') behind FORCE_HTTPS
COPY scripts/force_https_patch.php /tmp/force_https_patch.php
RUN php /tmp/force_https_patch.php /var/www/html \
 && rm /tmp/force_https_patch.php

# Skip settings bootstrap when no DB is available (eg. during image build)
COPY scripts/skip_settings_bootstrap.php /tmp/skip_settings_bootstrap.php
RUN php /tmp/skip_settings_bootstrap.php /var/www/html \
 && rm /tmp/skip_settings_bootstrap.php

# PHP deps
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
 && mv .env.dockerbuild .env

# Frontend build (tolerant)
RUN [ -f package-lock.json ] && npm ci || true
RUN [ -f package.json ] && npm run build || true

# Laravel perms + storage symlink
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap public \
 && php artisan storage:link || true

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Default command
CMD ["php-fpm", "-F"]


# =========================
# Stage: web (nginx)
# =========================
FROM nginx:1.27-alpine AS web
COPY --from=app /var/www/html /var/www/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
RUN test -d /var/www/html/public
