<x-docs-page
    key="selfhost/installation"
    description="Set up Event Schedule on your own server with this step-by-step installation guide. Learn how to configure the database, environment, and cron jobs."
    lede="Set up Event Schedule on your own server with this step-by-step guide. For automated installation, consider using Softaculous or Docker."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#requirements">Requirements</x-doc-nav-link>
        <x-doc-nav-link href="#database">1. Set Up Database</x-doc-nav-link>
        <x-doc-nav-link href="#download">2. Download Application</x-doc-nav-link>
        <x-doc-nav-link href="#permissions">3. Set File Permissions</x-doc-nav-link>
        <x-doc-nav-link href="#environment">4. Configure Environment</x-doc-nav-link>
        <x-doc-nav-link href="#cron">5. Set Up Cron Job</x-doc-nav-link>
        <x-doc-nav-link href="#verification">Verification</x-doc-nav-link>
        <x-doc-nav-link href="#push-notifications">Push notifications</x-doc-nav-link>
        <x-doc-nav-link href="#spam-protection">Spam protection</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#translations">Custom translations</x-doc-nav-link>
        <x-doc-nav-link href="#custom-links">Custom dashboard links</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This guide walks you through manually installing Event Schedule on your own server. There are five steps: create an empty MySQL database, extract the release files, set file permissions, run the browser-based setup wizard, and add the cron job. The wizard writes your configuration to <code class="doc-inline-code">.env</code> and creates the database tables for you, so there is nothing to import by hand.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">These steps describe a plain selfhosted install, where you own every schedule on the server. If you want to run a multi-tenant service where other people sign up and get their own subdomain and plan, follow the <a href="{{ route('marketing.docs.saas.setup') }}" class="doc-link">SaaS setup guide</a> instead.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Automated Installation Options</div>
            <p>For easier installation, you can use:</p>
            <ul class="doc-list mt-2">
                <li><a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="doc-link">Softaculous</a> - One-click installation on cPanel hosts</li>
                <li><a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="doc-link">Docker</a> - Containerized deployment with Docker Compose</li>
            </ul>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Every feature is included</div>
            <p>A selfhosted install is not a reduced edition. It resolves to the Enterprise feature set, so ticketing, check-in, custom fields, event graphics, webhooks, custom CSS, AI features and unlimited newsletters are all available with no plan to buy. A few controls only make sense on the hosted service (per-schedule email settings, subscription billing) and are hidden here.</p>
        </div>
    </section>

    <!-- Requirements -->
    <section id="requirements" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Requirements
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Before you begin, ensure your server meets the following requirements:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Minimum Version</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">PHP</span></td>
                        <td>8.2+</td>
                        <td>With required extensions (see below)</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">MySQL</span></td>
                        <td>5.7+ or MariaDB 10.3+</td>
                        <td>The only supported database; the setup wizard configures the MySQL connection</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Web Server</span></td>
                        <td>Apache or Nginx</td>
                        <td>With mod_rewrite or equivalent, and its document root on the <code class="doc-inline-code">public</code> directory</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">SSL Certificate</span></td>
                        <td>Required</td>
                        <td>Links are generated as <code class="doc-inline-code">https://</code> outside local environments, and session cookies are secure-only by default</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Cron</span></td>
                        <td>Every minute</td>
                        <td>Runs scheduled tasks and the queue worker (<a href="#cron" class="doc-link">step 5</a>); email and calendar sync stop without it</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Required PHP Extensions</h3>
        <ul class="doc-list">
            <li>BCMath</li>
            <li>Ctype</li>
            <li>Fileinfo</li>
            <li>Intl</li>
            <li>JSON</li>
            <li>Mbstring</li>
            <li>OpenSSL</li>
            <li>PDO (with MySQL driver)</li>
            <li>Tokenizer</li>
            <li>XML</li>
            <li>cURL</li>
            <li>GD</li>
            <li>MySQLi - used by the <span class="font-semibold text-gray-900 dark:text-white">Test</span> button in the setup wizard, which checks your credentials before migrations run</li>
            <li>Zip - used by backup export and import, and by <code class="doc-inline-code">php artisan app:update</code></li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">GD, not Imagick</div>
            <p>Image work (thumbnails, social images, event graphics) is done with GD, and generating an event graphic fails outright if GD is missing. Imagick is not used anywhere, so installing it is not a substitute.</p>
        </div>
    </section>

    <!-- 1. Set Up Database -->
    <section id="database" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
            </svg>
            1. Set Up the Database
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Create a MySQL database and user for Event Schedule. Run the following commands in your MySQL client:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>SQL</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">CREATE DATABASE</span> eventschedule;
<span class="code-keyword">CREATE USER</span> <span class="code-string">'eventschedule'</span>@<span class="code-string">'localhost'</span> <span class="code-keyword">IDENTIFIED BY</span> <span class="code-string">'change_me'</span>;
<span class="code-keyword">GRANT ALL PRIVILEGES ON</span> eventschedule.* <span class="code-keyword">TO</span> <span class="code-string">'eventschedule'</span>@<span class="code-string">'localhost'</span>;</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Leave the database completely empty. There is no schema to import: the setup wizard in step 4 runs the migrations and creates every table. The user needs full privileges on that database, because migrations create, alter and index tables.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Security Note</div>
            <p>Replace <code class="doc-inline-code">change_me</code> with a strong, unique password. Never use default or weak passwords in production. The setup wizard also requires a password, so a user with a blank password will not be accepted.</p>
        </div>
    </section>

    <!-- 2. Download Application -->
    <section id="download" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            2. Download the Application
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Download the latest release and extract it into the directory that will hold the install.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Download <a href="https://github.com/eventschedule/eventschedule/releases/latest" target="_blank" rel="noopener noreferrer" class="doc-link">eventschedule.zip</a> from the latest GitHub release</li>
            <li>Upload the zip file to your server</li>
            <li>Extract the contents to your web root directory</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Example: Extract to web root</span>
<span class="code-keyword">cd</span> /var/www
<span class="code-keyword">unzip</span> eventschedule.zip</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The archive has no wrapping folder inside it: <code class="doc-inline-code">app</code>, <code class="doc-inline-code">public</code>, <code class="doc-inline-code">storage</code> and the rest land directly in whatever directory you unzip into. So <code class="doc-inline-code">cd</code> into the directory you want the install to live in before extracting.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">No Composer or Node needed on the server</div>
            <p>The release zip is built with dependencies already installed and the frontend assets already compiled, so you do not run <code class="doc-inline-code">composer install</code> or <code class="doc-inline-code">npm run build</code> after extracting. Those are only needed if you install from a <code class="doc-inline-code">git clone</code> instead.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Web Root Configuration</div>
            <p>Your web server should point to the <code class="doc-inline-code">public</code> directory inside the install directory, not the install directory itself. Getting this wrong is the single most common cause of a broken install, and its symptoms are described under <a href="#troubleshooting" class="doc-link">troubleshooting</a>.</p>
        </div>
    </section>

    <!-- 3. Set File Permissions -->
    <section id="permissions" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            3. Set File Permissions
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Ensure the web server has proper permissions to write to storage and cache directories.</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">cd</span> /path/to/eventschedule
<span class="code-keyword">chmod</span> -R 755 storage
<span class="code-keyword">sudo chown</span> -R www-data:www-data storage bootstrap public</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">All three directories are needed: <code class="doc-inline-code">storage</code> holds logs, uploads and caches, <code class="doc-inline-code">bootstrap</code> holds the compiled config and route caches, and <code class="doc-inline-code">public</code> has to be writable because setup creates the <code class="doc-inline-code">public/storage</code> symlink that serves uploaded images.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">User Note</div>
            <p>The user <code class="doc-inline-code">www-data</code> is typical for Apache on Debian/Ubuntu. Your web server may run under a different user (e.g., <code class="doc-inline-code">nginx</code>, <code class="doc-inline-code">apache</code>, or <code class="doc-inline-code">http</code>). Check your server configuration. Docker images based on Alpine often have no <code class="doc-inline-code">www-data</code> name at all, only the numeric UID <code class="doc-inline-code">82</code>, so use <code class="doc-inline-code">chown -R 82:82 ...</code> there instead.</p>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">.env must be writable too</div>
            <p>The web-server user must also be able to write to the <code class="doc-inline-code">.env</code> file, not just <code class="doc-inline-code">storage</code>. The app writes its <code class="doc-inline-code">APP_KEY</code> there on the first request, and the setup wizard saves your database configuration to the same file, so include <code class="doc-inline-code">.env</code> in the ownership change above (e.g. <code class="doc-inline-code">sudo chown www-data:www-data .env</code>). If it is read-only, the wizard shows a warning at the top of the form and stops before touching the database rather than leaving you half configured.</p>
        </div>
    </section>

    <!-- 4. Configure Environment -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            4. Configure Environment
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Copy the example environment file to create your configuration:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">cp</span> .env.example .env</code></pre>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Leave APP_URL blank</div>
            <p>Do not set <code class="doc-inline-code">APP_URL</code> in <code class="doc-inline-code">.env</code> yourself. The setup wizard appears while it is blank (or if the database has no tables), and it writes the correct value for you once setup succeeds. You also do not need to fill in the database credentials by hand; just create the empty database from step 1 and enter its details in the wizard, which creates all the tables.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Now access your application at <code class="doc-inline-code">https://your-domain.com</code> in your browser. Because <code class="doc-inline-code">APP_URL</code> is still blank, every request is redirected to the setup wizard, which is the sign-up page. Work through it in order:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li><span class="font-semibold text-gray-900 dark:text-white">Enter the database connection:</span> MySQL Host, Port, Database, Username and Password, pre-filled from the <code class="doc-inline-code">DB_*</code> values in your <code class="doc-inline-code">.env</code>. All five are required, so a MySQL user with a blank password is not accepted.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Press Test.</span> The account fields below stay hidden until the connection succeeds, so this is not an optional check. If the database already contains an Event Schedule installation, Test says so and keeps the form disabled, which is what stops you overwriting an existing site.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Create the admin account:</span> Email, Full Name and a password of at least 8 characters. This first account becomes the instance admin.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Accept the selfhosting terms,</span> and optionally tick <span class="font-semibold text-gray-900 dark:text-white">Report errors to the developers to help us improve the app</span>, which sets <code class="doc-inline-code">REPORT_ERRORS=true</code> so crashes are sent to the developers.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Press Sign Up.</span> The wizard runs the migrations first, and only writes to <code class="doc-inline-code">.env</code> once they succeed, so a bad database never leaves you with a half-configured install. It then sets <code class="doc-inline-code">APP_URL</code> to the address you loaded the wizard on, sets <code class="doc-inline-code">APP_ENV=production</code>, saves the <code class="doc-inline-code">DB_*</code> values, and creates the <code class="doc-inline-code">public/storage</code> symlink.</li>
        </ol>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The wizard does not configure email</div>
            <p>Nothing on this screen sets up mail. <code class="doc-inline-code">MAIL_MAILER</code> ships as <code class="doc-inline-code">log</code>, which writes messages to <code class="doc-inline-code">storage/logs/laravel.log</code> and delivers nothing, so ticket confirmations and verification emails will silently go nowhere until you edit the <code class="doc-inline-code">MAIL_*</code> values yourself. See <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">Email Setup</a>.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything else is configured by editing <code class="doc-inline-code">.env</code> directly. If you ever run <code class="doc-inline-code">php artisan config:cache</code>, re-run it (or <code class="doc-inline-code">php artisan config:clear</code>) after each change, or the old values stay live.</p>

        <h3 id="user-accounts" class="doc-subheading">User Accounts and Registration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A selfhosted install is single user by default. The first account you create in the setup wizard becomes the instance admin, and after that the sign-up page is closed: visiting it sends you to the login page instead.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Most people never need to change this. Your audience does not need accounts to use the site: they can buy tickets, RSVP, leave post-event feedback, and submit fan photos, videos and comments as guests.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you do want other people to be able to register on your server, enable it in <code class="doc-inline-code">.env</code>:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>ALLOW_REGISTRATION=<span class="code-value">true</span></code></pre>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Anyone who registers can create schedules</div>
            <p>There is no separate attendee-only role: a registered user can create their own schedules and events on your server. Only turn this on for a server you control access to, such as one on a private network or behind an authenticating proxy. If you want to host separate, independent tenants, run in SaaS mode with <code class="doc-inline-code">IS_HOSTED=true</code> instead, which gives each schedule its own plan and settings.</p>
        </div>

        <h3 class="doc-subheading">HTTPS and Session Cookies</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once <code class="doc-inline-code">APP_ENV</code> is <code class="doc-inline-code">production</code>, which is what the wizard writes, every generated link uses <code class="doc-inline-code">https://</code>, and <code class="doc-inline-code">SESSION_SECURE_COOKIE</code> ships as <code class="doc-inline-code">true</code> so the session cookie is only sent over HTTPS. On a server reached over plain HTTP that combination looks like a broken login: the sign-in form accepts your password and returns you to the login page, because the browser never stored the session. Install a certificate, or for a local test install only, set <code class="doc-inline-code">SESSION_SECURE_COOKIE=false</code>.</p>

        <h3 id="reverse-proxy" class="doc-subheading">Running Behind a Reverse Proxy</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If Event Schedule sits behind a reverse proxy or CDN (Nginx, Apache, Cloudflare, or a control panel such as HestiaCP), tell it which proxies to trust so it reads the <code class="doc-inline-code">X-Forwarded-Proto</code> and <code class="doc-inline-code">X-Forwarded-For</code> headers those proxies set:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>TRUSTED_PROXIES=*</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Use <code class="doc-inline-code">*</code> to trust any proxy, or a comma-separated list of proxy IPs or CIDR ranges (for example <code class="doc-inline-code">10.0.0.0/8,192.168.1.1</code>) when the origin server is reachable directly from the internet.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Symptoms of a missing value</div>
            <p>Without this setting the application treats every request as plain HTTP even when the browser is on HTTPS, which can produce redirect loops, and it records the proxy's IP address as the visitor's IP in analytics and rate limiting.</p>
        </div>
    </section>

    <!-- 5. Set Up Cron Job -->
    <section id="cron" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            5. Set Up the Cron Job
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This step is not optional. One cron entry drives everything that happens on a timer rather than because somebody clicked: scheduled newsletters, reminder and feedback emails, calendar sync, and releasing unpaid ticket reservations back into stock. Email sent during a page request, such as a ticket confirmation, still goes out without it, so an install missing this line looks perfectly healthy while every timed job silently never runs.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following line to your server's crontab:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>* * * * * php /path/to/eventschedule/artisan schedule:run</code></pre>
        </div>

        <h3 class="doc-subheading">Adding the Cron Job</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To edit your crontab, run:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">crontab</span> -e</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the cron line at the end of the file, making sure to replace <code class="doc-inline-code">/path/to/eventschedule</code> with your actual installation path.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">cPanel Users</div>
            <p>If using cPanel, you can add cron jobs via the "Cron Jobs" section in your control panel without using the command line.</p>
        </div>

        <h3 class="doc-subheading">What the Scheduler Runs</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A single minutely cron entry is enough because the scheduler decides internally what is due. The main jobs:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>How often</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Queue worker</span>, which drains queued email, push and webhook jobs. It has nothing to do on the shipped <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> setting, where that work runs inside the web request instead</td>
                        <td>Every minute</td>
                    </tr>
                    <tr>
                        <td>Send scheduled newsletters</td>
                        <td>Every minute</td>
                    </tr>
                    <tr>
                        <td>Retry failed jobs</td>
                        <td>Every 5 minutes</td>
                    </tr>
                    <tr>
                        <td>Google, Outlook and CalDAV calendar sync, AI translation</td>
                        <td>Every 15 minutes</td>
                    </tr>
                    <tr>
                        <td>Release unpaid ticket reservations, expire waitlist offers, send feedback requests, appointment and carpool reminders, event graphic emails</td>
                        <td>Hourly</td>
                    </tr>
                    <tr>
                        <td>Renew calendar webhooks, prune old logs and backups, notify owners about new booking requests, fan content and poll options, run curator auto-imports</td>
                        <td>Daily</td>
                    </tr>
                    <tr>
                        <td>Refresh the GeoIP database used for visitor-location analytics</td>
                        <td>Monthly</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Confirming it works</div>
            <p>Run <code class="doc-inline-code">php /path/to/eventschedule/artisan schedule:run</code> by hand first. It prints the tasks it ran, or "No scheduled commands are ready to run", and any error it prints is what cron would have hit silently. To then prove cron itself is firing, append your own redirect to the crontab line (<code class="doc-inline-code">&gt;&gt; /path/to/cron.log 2&gt;&amp;1</code>) and check that file a couple of minutes later. If it stays empty, cron is not running the command: make sure the <code class="doc-inline-code">php</code> in your crontab is the same binary the site uses, and that the path to <code class="doc-inline-code">artisan</code> is absolute.</p>
        </div>
    </section>

    <!-- Verification -->
    <section id="verification" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Verification
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">After completing the installation, verify everything is working correctly:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li><span class="font-semibold text-gray-900 dark:text-white">Access the application:</span> Visit <code class="doc-inline-code">https://your-domain.com</code> and confirm the homepage loads</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Sign in:</span> Log in with the admin account you created in the wizard. There is no second registration step: sign-up is closed once that account exists, unless you set <a href="#user-accounts" class="doc-link"><code class="doc-inline-code">ALLOW_REGISTRATION=true</code></a></li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Create a schedule:</span> Create a test schedule and add an event, then open its public page to confirm images load</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Check the cron job:</span> Run <code class="doc-inline-code">php artisan schedule:run</code> once by hand and confirm it completes without an error, then check the redirect file from <a href="#cron" class="doc-link">step 5</a> to confirm cron is calling it too</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Check logs:</span> Review <code class="doc-inline-code">storage/logs/laravel.log</code> for any errors</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Need Help?</div>
            <p>If you encounter any issues during installation, check the <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer" class="doc-link">GitHub Issues</a> or start a <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="doc-link">Discussion</a>.</p>
        </div>

        <h3 class="doc-subheading">Next Steps</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Now that Event Schedule is installed, you may want to:</p>
        <ul class="doc-list">
            <li>Configure <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">email delivery</a>, which nothing else works properly without</li>
            <li>Configure <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Stripe payments</a> for ticket sales</li>
            <li>Add a <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">Gemini or OpenAI key</a> to turn on AI event import, agenda scanning and translation</li>
            <li>Set up <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="doc-link">Google Calendar integration</a></li>
            <li>Set up <a href="{{ route('marketing.docs.selfhost.microsoft_calendar') }}" class="doc-link">Outlook Calendar integration</a></li>
            <li>Set up <a href="{{ route('marketing.docs.saas.twilio') }}" class="doc-link">Twilio</a> to text invitations to venues or talent you added by phone number, and to send WhatsApp messages (<code class="doc-inline-code">TWILIO_SID</code>, <code class="doc-inline-code">TWILIO_AUTH_TOKEN</code>, <code class="doc-inline-code">TWILIO_FROM_NUMBER</code>)</li>
            <li>Enable <a href="#push-notifications" class="doc-link">push notifications</a> with OneSignal (optional)</li>
            <li>Add a <a href="#spam-protection" class="doc-link">Turnstile challenge</a> to your public forms (optional)</li>
            <li>Turn on <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">federation</a> to share your public events with the eventschedule.com listings (optional, off by default)</li>
            <li>Tour the <a href="{{ route('marketing.docs.selfhost.admin') }}" class="doc-link">admin panel</a>, where you can watch the queue, read logs, edit translations and change platform settings</li>
        </ul>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Keeping the install up to date</div>
            <p>Upgrades are one step, from either direction: open <span class="font-semibold text-gray-900 dark:text-white">Settings &gt; App Update</span>, which shows your installed version next to the latest release and offers an <span class="font-semibold text-gray-900 dark:text-white">Update</span> button when they differ, or run <code class="doc-inline-code">php artisan app:update</code> on the server. Both download and install the new release and then run any new migrations. Take a backup first. Your uploads, custom translations and anything else under <code class="doc-inline-code">storage/app/</code> are excluded from the update by design, so they survive it. Instance admins get the same panel at <span class="font-semibold text-gray-900 dark:text-white">Admin &gt; System &gt; App Update</span>, which also badges the System menu when a release is waiting.</p>
            <p class="mt-3">If the App Update screen is not there at all, use the command: it works on every install and does not depend on the screen. That is also the way back from an older release whose UI hid the update button.</p>
        </div>
    </section>

    <!-- Push Notifications -->
    <section id="push-notifications" class="doc-section">
        <h2 class="doc-heading">
            <span class="doc-heading-icon">
                <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            </span>
            Push Notifications (Optional)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Event Schedule can send browser and mobile web push notifications alongside the emails it already sends, using <a href="https://onesignal.com" target="_blank" rel="noopener noreferrer" class="doc-link">OneSignal</a>. The same moments trigger both: a ticket sale, a booking request accepted or declined, new feedback, a waitlist opening, a finished backup export or import. Push is <strong>off by default</strong>: if you do not configure it, no push SDK is loaded and your installation makes no calls to OneSignal.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To enable it, create a free OneSignal app (Web platform), then set these values in your <code class="doc-inline-code">.env</code>:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>ONESIGNAL_APP_ID=your-onesignal-app-id
ONESIGNAL_REST_API_KEY=your-onesignal-rest-api-key</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Both values are needed; with only one set, push stays off. There is also an optional <code class="doc-inline-code">ONESIGNAL_SAFARI_WEB_ID</code>, which you only need for legacy macOS Safari web push.</p>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">What turning this on shares</div>
            <p>Enabling OneSignal loads its SDK from OneSignal's CDN and sends notification data to OneSignal's servers. Visitors choose to opt in per device; nothing is sent until they allow notifications.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once configured, a schedule's settings gain a <strong>Push notifications</strong> panel on the <strong>Notifications</strong> tab, with <strong>Enable push on this device</strong> and, after that, <strong>Send test push</strong>. Opting in is per device, so each browser you want alerts on has to be enabled separately. Apple iOS only supports web push for sites the visitor adds to their home screen (iOS 16.4+); Android and desktop browsers work without installation.</p>
    </section>

    <!-- Spam protection -->
    <section id="spam-protection" class="doc-section">
        <h2 class="doc-heading">
            <span class="doc-heading-icon">
                <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
            </span>
            Spam Protection (Optional)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Event Schedule can put a <a href="https://www.cloudflare.com/products/turnstile/" target="_blank" rel="noopener noreferrer" class="doc-link">Cloudflare Turnstile</a> challenge in front of every form a stranger can reach. Turnstile is invisible to most visitors and needs no puzzle-solving.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once configured, the challenge is added to:</p>
        <ul class="doc-list mb-6">
            <li>Sign in, sign up and password reset</li>
            <li>Ticket checkout and gift card purchases</li>
            <li>RSVPs and appointment bookings</li>
            <li>Events submitted by guests through a schedule's submission page</li>
            <li>Fan photo, video and comment submissions</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Create a free Turnstile widget for your domain, then set both values in your <code class="doc-inline-code">.env</code>:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>TURNSTILE_SITE_KEY=your-turnstile-site-key
TURNSTILE_SECRET_KEY=your-turnstile-secret-key</code></pre>
        </div>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Both keys are required</div>
            <p>If either one is missing the challenge is skipped entirely, so a half-filled configuration leaves those forms unprotected without any warning. Enabling Turnstile loads Cloudflare's widget script on the affected pages.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Turnstile is deliberately inactive on tenant custom domains, because a site key is registered against specific hostnames and would fail to validate on a domain you do not control. If you run a multi-tenant SaaS with <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="doc-link">custom domains</a>, expect those pages to fall back to no challenge.</p>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">"Permission denied" writing storage/logs/laravel.log</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The web-server user cannot write to <code class="doc-inline-code">storage</code> (and often <code class="doc-inline-code">.env</code>). Re-run the ownership and permission commands from the <a href="#permissions" class="doc-link">file permissions</a> step, making sure to use the user your web server actually runs as. On Alpine-based Docker images that is the numeric UID <code class="doc-inline-code">82</code>, not <code class="doc-inline-code">www-data</code>.</p>

        <h3 class="doc-subheading">A 500 error, or you can't get back to the setup wizard</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">This usually means the database has no tables yet (migrations did not run). The setup wizard reappears automatically whenever the database is empty, so fix the underlying cause (database privileges or the file permissions above) and reload the page to run setup again.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Manual fallback</div>
            <p>If the wizard still does not appear, open <code class="doc-inline-code">.env</code>, clear the <code class="doc-inline-code">APP_URL</code> value so it is blank, and reload. The wizard will run again and rewrite <code class="doc-inline-code">APP_URL</code> once setup succeeds.</p>
        </div>

        <h3 class="doc-subheading">No email is ever delivered</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Start with <code class="doc-inline-code">MAIL_MAILER</code>, which ships as <code class="doc-inline-code">log</code>. That writes the whole message into <code class="doc-inline-code">storage/logs/laravel.log</code> instead of sending it, so finding your "missing" emails in that file confirms the diagnosis. Set your real mail credentials as described in <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">Email Setup</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">If only the timed messages are missing (reminders, feedback requests, scheduled newsletters) while ticket confirmations arrive normally, the mail configuration is fine and the <a href="#cron" class="doc-link">cron job</a> is not running. If you have switched <code class="doc-inline-code">QUEUE_CONNECTION</code> away from the shipped <code class="doc-inline-code">sync</code> value, a growing <code class="doc-inline-code">jobs</code> table is the same symptom: nothing is draining the queue.</p>

        <h3 class="doc-subheading">Uploaded images do not load</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Uploads are served through a <code class="doc-inline-code">public/storage</code> symlink that setup creates for you. If <code class="doc-inline-code">public</code> was not writable at the time, setup carries on without it and images 404 afterwards. Fix the ownership from the <a href="#permissions" class="doc-link">file permissions</a> step, then create the link yourself with <code class="doc-inline-code">php artisan storage:link</code>.</p>

        <h3 class="doc-subheading">Ticket QR codes don't scan, or the site answers on <code class="doc-inline-code">/public</code></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Your web server's document root must point at the <code class="doc-inline-code">public</code> directory inside the project, not at the project folder itself. When the root is set one level too high, the app answers on both <code class="doc-inline-code">https://your-domain.com/</code> and <code class="doc-inline-code">https://your-domain.com/public/</code>, and links generated from a page you reached through <code class="doc-inline-code">/public/</code> carry that segment too.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The give-away is that the same ticket shows one QR code when opened from a confirmation email and a different one when opened from the Sales page. Fix the document root, then confirm that <code class="doc-inline-code">APP_URL</code> in <code class="doc-inline-code">.env</code> exactly matches the address people use to reach the site. <code class="doc-inline-code">APP_URL</code> is what the app trusts when it builds the URL inside a ticket's QR code, so it must include a sub-path if you genuinely serve the app from one.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Already-issued QR codes keep the old address</div>
            <p>QR codes printed or emailed before you correct <code class="doc-inline-code">APP_URL</code> keep pointing at the old address. Re-send the ticket email to reissue them.</p>
        </div>
    </section>

    <!-- Custom translations -->
    <section id="translations" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Custom translations
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Rename built-in UI terms (for example "Talent" to "Artist", or "Curator" to "Event Planner") without your changes being wiped out by <code class="doc-inline-code">php artisan app:update</code>.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Prefer the admin panel</div>
            <p>The easiest way to customize translations is the <a href="{{ route('marketing.docs.selfhost.admin') }}#system-translations" class="doc-link">Translations page</a> in the admin panel (System &gt; Translations): search every string, edit any language, and optionally share improvements with the community. Hand-made files described below keep working and are adopted into the editor automatically.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Drop a PHP file in:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>path</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>storage/app/lang/{locale}/{file}.php</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The admin editor manages three files: <code class="doc-inline-code">messages.php</code> (UI strings), <code class="doc-inline-code">accessibility.php</code>, and <code class="doc-inline-code">marketing.php</code>. List the keys you want to change and nothing else; the bundled translations fill in the rest:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>php</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>&lt;?php
// storage/app/lang/en/messages.php
return [
'talent' =&gt; 'Artist',
'talents' =&gt; 'Artists',
'curator' =&gt; 'Event Planner',
'curators' =&gt; 'Event Planners',
];</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Create one directory per locale you want to override (<code class="doc-inline-code">en</code>, <code class="doc-inline-code">es</code>, <code class="doc-inline-code">fr</code>, &hellip;). The full list of supported locales lives in <code class="doc-inline-code">config/app.php</code> under <code class="doc-inline-code">supported_languages</code>.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Files for any other group, such as <code class="doc-inline-code">validation.php</code> or <code class="doc-inline-code">auth.php</code>, are honored the same way. They simply sit outside the admin editor, which never rewrites or prunes them.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why this works</div>
            <p>Changes apply on the next request, no cache clear is required. <code class="doc-inline-code">storage/app/</code> is gitignored, so your overrides survive <code class="doc-inline-code">php artisan app:update</code>, <code class="doc-inline-code">git pull</code>, and fresh checkouts. New keys added in future releases continue to show their bundled English (or translated) value until you override them.</p>
        </div>
    </section>

    <!-- Custom dashboard links -->
    <section id="custom-links" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Custom dashboard links
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add up to three custom links to the sidebar of the admin portal (for example a support site, community forum, or internal tool). They appear for everyone signed in, just below the <span class="font-semibold text-gray-900 dark:text-white">Newsletters</span> link, and open in a new tab. This works in both selfhosted and SaaS deployments.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Set the following variables in your <code class="doc-inline-code">.env</code> file. A link only appears when <span class="font-semibold text-gray-900 dark:text-white">both</span> its title and URL are filled in, so you can configure one, two, or three links:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>CUSTOM_LINK_1_TITLE=<span class="code-string">"Support"</span>
CUSTOM_LINK_1_URL=<span class="code-string">"https://support.example.com"</span>
CUSTOM_LINK_2_TITLE=<span class="code-string">"Community"</span>
CUSTOM_LINK_2_URL=<span class="code-string">"https://community.example.com"</span>
CUSTOM_LINK_3_TITLE=
CUSTOM_LINK_3_URL=</code></pre>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Reload cached config</div>
            <p>If you have run <code class="doc-inline-code">php artisan config:cache</code>, re-run it (or <code class="doc-inline-code">php artisan config:clear</code>) after editing <code class="doc-inline-code">.env</code> so the new links take effect.</p>
        </div>
    </section>
</x-docs-page>
