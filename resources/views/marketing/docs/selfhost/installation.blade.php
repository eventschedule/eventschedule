<x-marketing-layout>
    <x-slot name="title">Installation Guide - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Installation</x-slot>
    <x-slot name="description">Set up Event Schedule on your own server with this step-by-step installation guide. Learn how to configure the database, environment, and cron jobs.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Installation Guide - Event Schedule",
        "description": "Set up Event Schedule on your own server with this step-by-step installation guide. Learn how to configure the database, environment, and cron jobs.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Installation Guide" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Installation Guide</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up Event Schedule on your own server with this step-by-step guide. For automated installation, consider using <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Softaculous</a> or <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Docker</a>.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#requirements" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Requirements</a>
                        <a href="#database" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">1. Set Up Database</a>
                        <a href="#download" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">2. Download Application</a>
                        <a href="#permissions" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">3. Set File Permissions</a>
                        <a href="#environment" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">4. Configure Environment</a>
                        <a href="#cron" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">5. Set Up Cron Job</a>
                        <a href="#verification" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Verification</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">This guide walks you through manually installing Event Schedule on your own server. The installation process involves setting up a database, downloading the application files, configuring permissions, and setting up scheduled tasks.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Automated Installation Options</div>
                                <p>For easier installation, you can use:</p>
                                <ul class="doc-list mt-2">
                                    <li><a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Softaculous</a> - One-click installation on cPanel hosts</li>
                                    <li><a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Docker</a> - Containerized deployment with Docker Compose</li>
                                </ul>
                            </div>
                        </section>

                        <!-- Requirements -->
                        <section id="requirements" class="doc-section">
                            <h2 class="doc-heading">Requirements</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Before you begin, ensure your server meets the following requirements:</p>

                            <div class="overflow-x-auto mb-6">
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
                                            <td>8.1+</td>
                                            <td>With required extensions (see below)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">MySQL</span></td>
                                            <td>5.7+ or MariaDB 10.3+</td>
                                            <td>For database storage</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Web Server</span></td>
                                            <td>Apache or Nginx</td>
                                            <td>With mod_rewrite or equivalent</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">SSL Certificate</span></td>
                                            <td>Required</td>
                                            <td>HTTPS is required for security</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Required PHP Extensions</h3>
                            <ul class="doc-list">
                                <li>BCMath</li>
                                <li>Ctype</li>
                                <li>Fileinfo</li>
                                <li>JSON</li>
                                <li>Mbstring</li>
                                <li>OpenSSL</li>
                                <li>PDO (with MySQL driver)</li>
                                <li>Tokenizer</li>
                                <li>XML</li>
                                <li>cURL</li>
                                <li>GD or Imagick</li>
                            </ul>
                        </section>

                        <!-- 1. Set Up Database -->
                        <section id="database" class="doc-section">
                            <h2 class="doc-heading">1. Set Up the Database</h2>
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

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Security Note</div>
                                <p>Replace <code class="doc-inline-code">change_me</code> with a strong, unique password. Never use default or weak passwords in production.</p>
                            </div>
                        </section>

                        <!-- 2. Download Application -->
                        <section id="download" class="doc-section">
                            <h2 class="doc-heading">2. Download the Application</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Download the latest release and extract it to your web server's document root.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Download <a href="https://github.com/eventschedule/eventschedule/releases/latest" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">eventschedule.zip</a> from the latest GitHub release</li>
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

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Web Root Configuration</div>
                                <p>Your web server should point to the <code class="doc-inline-code">public</code> directory inside the extracted folder, not the root directory itself.</p>
                            </div>
                        </section>

                        <!-- 3. Set File Permissions -->
                        <section id="permissions" class="doc-section">
                            <h2 class="doc-heading">3. Set File Permissions</h2>
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

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">User Note</div>
                                <p>The user <code class="doc-inline-code">www-data</code> is typical for Apache on Debian/Ubuntu. Your web server may run under a different user (e.g., <code class="doc-inline-code">nginx</code>, <code class="doc-inline-code">apache</code>, or <code class="doc-inline-code">http</code>). Check your server configuration.</p>
                            </div>
                        </section>

                        <!-- 4. Configure Environment -->
                        <section id="environment" class="doc-section">
                            <h2 class="doc-heading">4. Configure Environment</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Copy the example environment file to create your configuration:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">cp</span> .env.example .env</code></pre>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Now access your application at <code class="doc-inline-code">https://your-domain.com</code> in your browser. You'll see the setup wizard where you can configure:</p>

                            <ul class="doc-list mb-6">
                                <li>Database connection details</li>
                                <li>Application name and URL</li>
                                <li>Email settings</li>
                                <li>Admin account credentials</li>
                            </ul>

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <p class="text-gray-600 dark:text-gray-300 text-sm">The setup wizard will guide you through the initial configuration and run database migrations automatically.</p>
                            </div>
                        </section>

                        <!-- 5. Set Up Cron Job -->
                        <section id="cron" class="doc-section">
                            <h2 class="doc-heading">5. Set Up the Cron Job</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule requires a cron job to run scheduled tasks like sending reminder emails, syncing calendars, and releasing expired ticket reservations.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following line to your server's crontab:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>crontab</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>* * * * * php /path/to/eventschedule/artisan schedule:run</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Adding the Cron Job</h3>
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
                        </section>

                        <!-- Verification -->
                        <section id="verification" class="doc-section">
                            <h2 class="doc-heading">Verification</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">After completing the installation, verify everything is working correctly:</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><span class="font-semibold text-gray-900 dark:text-white">Access the application:</span> Visit <code class="doc-inline-code">https://your-domain.com</code> and confirm the homepage loads</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Create an account:</span> Register a new user account to verify database connectivity</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Create a schedule:</span> Create a test schedule and add an event</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Check logs:</span> Review <code class="doc-inline-code">storage/logs/laravel.log</code> for any errors</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Need Help?</div>
                                <p>If you encounter any issues during installation, check the <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">GitHub Issues</a> or start a <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Discussion</a>.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Next Steps</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Now that Event Schedule is installed, you may want to:</p>
                            <ul class="doc-list">
                                <li>Configure <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="text-cyan-400 hover:text-cyan-300">Stripe payments</a> for ticket sales</li>
                                <li>Set up <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="text-cyan-400 hover:text-cyan-300">Google Calendar integration</a></li>
                                <li>Set up <a href="https://www.twilio.com" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:text-cyan-300">Twilio SMS</a> for phone verification (set <code class="doc-inline-code">TWILIO_SID</code>, <code class="doc-inline-code">TWILIO_AUTH_TOKEN</code>, and <code class="doc-inline-code">TWILIO_FROM_NUMBER</code> in your <code class="doc-inline-code">.env</code>)</li>
                                <li>Configure email settings for notifications</li>
                                <li>Customize your branding and appearance</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
