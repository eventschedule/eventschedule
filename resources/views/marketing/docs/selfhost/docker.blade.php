<x-docs-page
    key="selfhost/docker"
    title="Selfhost Event Schedule with Docker Compose - Event Schedule"
    description="Run Event Schedule with Docker Compose: build the image, fix the application key, serve it over HTTPS behind a reverse proxy, add email and Stripe, back up and update."
    lede="Run your own event calendar and ticketing with Docker Compose: four containers, one .env file, and no PHP or cron to set up on the host."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#requirements">Requirements</x-doc-nav-link>
        <x-doc-nav-link href="#get-files">1. Get the Docker Files</x-doc-nav-link>
        <x-doc-nav-link href="#app-key">2. Fix the Application Key</x-doc-nav-link>
        <x-doc-nav-link href="#start">3. Start the Stack</x-doc-nav-link>
        <x-doc-nav-link href="#https">4. Serve It Over HTTPS</x-doc-nav-link>
        <x-doc-nav-link href="#email">5. Set Up Email</x-doc-nav-link>
        <x-doc-nav-link href="#payments">6. Sell Tickets</x-doc-nav-link>
        <x-doc-nav-link href="#backups">Backups</x-doc-nav-link>
        <x-doc-nav-link href="#updating">Updating</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
    </x-slot:toc>

    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="doc-link">eventschedule/dockerfiles</a> repository runs Event Schedule with Docker Compose. It builds the app from source and starts four containers, so there is no PHP, web server or cron to set up on the host.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">app</span></td>
                        <td>PHP-FPM running the application. Runs the database migrations each time it starts.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">web</span></td>
                        <td>Nginx, serving the app on port 8080 of the host.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">db</span></td>
                        <td>MariaDB 11, with its data in the <code class="doc-inline-code">dbdata</code> volume.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">scheduler</span></td>
                        <td>Runs the app's scheduled tasks every minute: reminders, scheduled newsletters, calendar sync and the queue. This is the cron entry of a manual install.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A Docker install is a full selfhosted install: every feature is on, with no plan or license key. The <a href="{{ route('marketing.docs.selfhost') }}#differences" class="doc-link">Selfhost overview</a> lists how it differs from the hosted service. To install without Docker, follow the <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">manual installation guide</a>.</p>
    </section>

    <section id="requirements" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Requirements
        </h2>
        <ul class="doc-list">
            <li>Docker Engine 24 or newer, with the Compose v2 plugin (<code class="doc-inline-code">docker compose</code>, not <code class="doc-inline-code">docker-compose</code>)</li>
            <li>Internet access on the machine that builds the image: the build downloads the app's source and its PHP and JavaScript dependencies</li>
            <li>For anything beyond a local test, a domain name and a reverse proxy that serves HTTPS (<a href="#https" class="doc-link">step 4</a>)</li>
        </ul>
    </section>

    <section id="get-files" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
            </svg>
            1. Get the Docker Files
        </h2>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>git clone https://github.com/eventschedule/dockerfiles.git eventschedule
cd eventschedule</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The repository ships a working <code class="doc-inline-code">.env</code> for a local test. Open it and set the database password, and the address you will open the app at:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>DB_PASSWORD=pick-a-long-random-password
APP_URL=http://localhost:8080</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Docker Compose reads the same <code class="doc-inline-code">DB_PASSWORD</code> to create the database user, so it is set in one place. Leave <code class="doc-inline-code">APP_URL</code> as it is for a local test; for a real domain it changes in <a href="#https" class="doc-link">step 4</a>.</p>
    </section>

    <section id="app-key" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            2. Fix the Application Key
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><code class="doc-inline-code">APP_KEY</code> encrypts sessions and the credentials saved in the app, such as a schedule's mail password. The image generates one when it is built, so a later rebuild generates a different one: everybody is signed out and data encrypted with the old key can no longer be read. Generate a key once and keep it in <code class="doc-inline-code">.env</code>:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>docker compose run --rm app php artisan key:generate --show</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">This first command also builds the image, which takes several minutes. The last line it prints is the key, starting with <code class="doc-inline-code">base64:</code>. Uncomment the <code class="doc-inline-code">APP_KEY=</code> line in <code class="doc-inline-code">.env</code> and paste it in:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>APP_KEY=base64:your-key-here</code></pre>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Keep a copy of the key</div>
            <p>Store <code class="doc-inline-code">.env</code> with your backups. A database restored without the key it was encrypted with loses every saved credential.</p>
        </div>
    </section>

    <section id="start" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            3. Start the Stack
        </h2>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>docker compose up -d
docker compose ps</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The app container waits for the database, runs the migrations and starts. Follow it with <code class="doc-inline-code">docker compose logs -f app</code> if it takes a while.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Then open <code class="doc-inline-code">http://localhost:8080/sign_up</code> and create your account. The first account on the install becomes its admin, and public sign-up closes once it exists: you add people from a schedule's Team tab, or reopen sign-up with <code class="doc-inline-code">ALLOW_REGISTRATION=true</code> in <code class="doc-inline-code">.env</code>.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">No setup wizard</div>
            <p>A manual install opens on a browser setup wizard. The Docker install does not need it: the database is configured by Compose, and the migrations have already run, so you go straight to creating your account.</p>
        </div>
    </section>

    <section id="https" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            4. Serve It Over HTTPS
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Outside a local test, put the app behind a reverse proxy that serves HTTPS. Event Schedule generates <code class="doc-inline-code">https://</code> links whenever <code class="doc-inline-code">APP_ENV</code> is not <code class="doc-inline-code">local</code>, so these settings change together in <code class="doc-inline-code">.env</code>:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>APP_ENV=production
APP_URL=https://events.example.com
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><code class="doc-inline-code">TRUSTED_PROXIES</code> lets the app believe the proxy when it says the visit came in over HTTPS; without it every page redirects to itself. <code class="doc-inline-code">*</code> is safe when only your proxy can reach port 8080; otherwise list the proxy's address. <a href="{{ route('marketing.docs.selfhost.installation') }}#reverse-proxy" class="doc-link">Running behind a reverse proxy</a> has the details.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">With Caddy, the whole proxy configuration is:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>Caddyfile</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>events.example.com {
    reverse_proxy localhost:8080
}</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">For SWAG, the repository includes <code class="doc-inline-code">eventschedule.subdomain.conf.sample</code> for its proxy-confs folder. Nginx Proxy Manager and Traefik work the same way: point them at port 8080. When the proxy runs on the same machine, change the <code class="doc-inline-code">web</code> service's port in <code class="doc-inline-code">docker-compose.yml</code> to <code class="doc-inline-code">"127.0.0.1:8080:80"</code> so the app is not reachable from outside around it.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Containers read <code class="doc-inline-code">.env</code> when they are created, so apply any change to it with:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>docker compose up -d</code></pre>
        </div>
    </section>

    <section id="email" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            5. Set Up Email
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Email carries ticket confirmations, password resets and team invites, so set it up before you sell anything. Add your SMTP server to <code class="doc-inline-code">.env</code>:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=you@example.com
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=events@example.com
MAIL_FROM_NAME="Your Venue"</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Port 465 uses SSL and any other port upgrades with STARTTLS. Run <code class="doc-inline-code">docker compose up -d</code> to apply it. The <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">Email guide</a> covers the other drivers and what is sent when.</p>
    </section>

    <section id="payments" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            6. Sell Tickets
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">On a selfhosted install, the money from every ticket sale goes to one Stripe account that you control, and event creators have nothing to connect. Add your keys and the webhook's signing secret to <code class="doc-inline-code">.env</code>:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>STRIPE_PLATFORM_KEY=pk_live_...
STRIPE_PLATFORM_SECRET=sk_live_...
STRIPE_PLATFORM_WEBHOOK_SECRET=whsec_...</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The webhook points at <code class="doc-inline-code">https://events.example.com/stripe/webhook</code> with the <code class="doc-inline-code">checkout.session.completed</code> event; until its secret is set, no sale is marked paid. After <code class="doc-inline-code">docker compose up -d</code>, edit an event, open its Tickets tab, choose Sell tickets and set the payment method to Stripe. The <a href="{{ route('marketing.docs.selfhost.stripe') }}#selfhosted-users" class="doc-link">Payments guide</a> walks through each step, and covers PayPal, Payfast, Razorpay and Invoice Ninja.</p>
    </section>

    <section id="backups" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            Backups
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Everything is in two volumes: <code class="doc-inline-code">dbdata</code> (the database) and <code class="doc-inline-code">storage</code> (uploaded images and files). Compose prefixes them with the folder name, so in an <code class="doc-inline-code">eventschedule</code> folder they are <code class="doc-inline-code">eventschedule_dbdata</code> and <code class="doc-inline-code">eventschedule_storage</code>.</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code># The database
docker compose exec db sh -c 'mariadb-dump -ueventschedule -p"$MYSQL_PASSWORD" eventschedule' &gt; eventschedule-$(date +%F).sql

# Uploaded files
docker run --rm -v eventschedule_storage:/data -v "$PWD":/backup alpine \
  tar czf /backup/storage-$(date +%F).tar.gz -C /data .</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Keep <code class="doc-inline-code">.env</code> with them: it holds the <a href="#app-key" class="doc-link">application key</a> the saved credentials are encrypted with.</p>
    </section>

    <section id="updating" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Updating
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The image copies Event Schedule's source when it is built, so updating means building it again. Docker caches that step, so build without the cache, then recreate the containers:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>git pull
docker compose build --no-cache
docker compose up -d</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><code class="doc-inline-code">git pull</code> picks up changes to the Docker files themselves. The migrations run when the app container starts. Take a <a href="#backups" class="doc-link">backup</a> first.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Not the Update button</div>
            <p>Settings &gt; App Update still offers an Update button in Docker. Do not use it there: it rewrites the files inside the running app container, which are replaced by the image the next time the container is created, and the <code class="doc-inline-code">web</code> container keeps serving the old build. Rebuild instead.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The image builds from the <code class="doc-inline-code">main</code> branch. To stay on a release instead, set <code class="doc-inline-code">APP_REF</code> to a release tag such as <code class="doc-inline-code">v1.0.140</code> for both the <code class="doc-inline-code">app</code> and <code class="doc-inline-code">scheduler</code> services in <code class="doc-inline-code">docker-compose.yml</code>, and change it when you update. The <a href="https://github.com/eventschedule/eventschedule/releases" target="_blank" rel="noopener noreferrer" class="doc-link">release notes</a> list what each one changes.</p>
    </section>

    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            Troubleshooting
        </h2>
<h3 class="doc-subheading">Every page redirects to itself</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The app is behind a proxy it does not trust, so it believes each visit came in over plain HTTP and sends it to HTTPS again. Set <code class="doc-inline-code">TRUSTED_PROXIES</code> as in <a href="#https" class="doc-link">step 4</a>.</p>

<h3 class="doc-subheading">Links in emails point to localhost</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><code class="doc-inline-code">APP_URL</code> is still <code class="doc-inline-code">http://localhost:8080</code>. Set it to the address people use, then run <code class="doc-inline-code">docker compose up -d</code>.</p>

<h3 class="doc-subheading">Everybody was signed out after an update</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The rebuilt image generated a new application key. Put a fixed key in <code class="doc-inline-code">.env</code> as in <a href="#app-key" class="doc-link">step 2</a>. If the install ran with a key you did not save, credentials saved under it have to be entered again.</p>

<h3 class="doc-subheading">Page Expired when signing in</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4"><code class="doc-inline-code">SESSION_SECURE_COOKIE=true</code> needs HTTPS. On a plain <code class="doc-inline-code">http://</code> address, such as a local test, keep it <code class="doc-inline-code">false</code>.</p>

<h3 class="doc-subheading">Reminders and scheduled newsletters are not sent</h3>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Check that the scheduler is running with <code class="doc-inline-code">docker compose ps</code>, and read its output with <code class="doc-inline-code">docker compose logs -f scheduler</code>.</p>
    </section>
</x-docs-page>
