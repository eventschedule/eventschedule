<x-marketing-layout>
    <x-slot name="title">SaaS Setup Documentation - Event Schedule</x-slot>
    <x-slot name="description">Learn how to configure Event Schedule for SaaS deployment with subdomain-based multi-tenant routing, custom branding, and Stripe subscriptions.</x-slot>
    <x-slot name="keywords">saas setup, multi-tenant, subdomain routing, white-label, event schedule deployment</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="SaaS Setup" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">SaaS Installation Setup</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Configure Event Schedule for SaaS (Software as a Service) deployment, where you host the platform for multiple customers using subdomains.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#prerequisites" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Prerequisites</a>
                        <a href="#environment" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Environment Configuration</a>
                        <a href="#dns" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">DNS Configuration</a>
                        <a href="#webserver" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Web Server Configuration</a>
                        <a href="#stripe" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Stripe Subscription Setup</a>
                        <a href="#example" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Complete Example</a>
                        <a href="#verification" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Verification Steps</a>
                        <a href="#demo" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Demo Mode</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                        <a href="#security" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Security Considerations</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-300 mb-6">Event Schedule supports two deployment modes:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Mode</th>
                                            <th>Routing</th>
                                            <th>Use Case</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-white">Self-hosted</span></td>
                                            <td>Path-based <code class="doc-inline-code">/schedule-name/...</code></td>
                                            <td>Single organization or personal use</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">SaaS/Hosted</span></td>
                                            <td>Subdomain-based <code class="doc-inline-code">schedule-name.yourdomain.com</code></td>
                                            <td>Multi-tenant platform for multiple customers</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-300">In SaaS mode, each customer (schedule/role) gets their own subdomain, and the main domain can display marketing pages to attract new signups.</p>
                        </section>

                        <!-- Prerequisites -->
                        <section id="prerequisites" class="doc-section">
                            <h2 class="doc-heading">Prerequisites</h2>
                            <ol class="doc-list doc-list-numbered">
                                <li>Completed base installation of Event Schedule</li>
                                <li>A domain name with DNS access</li>
                                <li>Ability to configure wildcard SSL certificates</li>
                                <li>Web server configured to handle wildcard subdomains (Apache or Nginx)</li>
                            </ol>
                        </section>

                        <!-- Environment Configuration -->
                        <section id="environment" class="doc-section">
                            <h2 class="doc-heading">Environment Configuration</h2>
                            <p class="text-gray-300 mb-6">Add the following variables to your <code class="doc-inline-code">.env</code> file to enable SaaS mode:</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Core SaaS Settings</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Enable SaaS mode with subdomain routing</span>
<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span>

<span class="code-comment"># Your application name (shown in emails and alt text)</span>
<span class="code-variable">APP_NAME</span>=<span class="code-string">Your Platform Name</span>

<span class="code-comment"># Main application URL</span>
<span class="code-variable">APP_URL</span>=<span class="code-string">https://yourdomain.com</span>

<span class="code-comment"># Marketing site URL (can be same as APP_URL)</span>
<span class="code-variable">APP_MARKETING_URL</span>=<span class="code-string">https://yourdomain.com</span></code></pre>
                            </div>

                            <div class="overflow-x-auto mb-8">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Default</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">IS_HOSTED</code></td>
                                            <td><code class="doc-inline-code">false</code></td>
                                            <td>Enable subdomain-based routing for multi-tenant SaaS</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">APP_NAME</code></td>
                                            <td><code class="doc-inline-code">Laravel</code></td>
                                            <td>Brand name shown in emails, page titles, and image alt text</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">APP_URL</code></td>
                                            <td>-</td>
                                            <td>Primary application URL</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">APP_MARKETING_URL</code></td>
                                            <td><code class="doc-inline-code">https://eventschedule.com</code></td>
                                            <td>URL for marketing site links</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Branding Customization</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Logo for light backgrounds (header, emails)</span>
<span class="code-variable">APP_LOGO_DARK</span>=<span class="code-string">/images/dark_logo.png</span>

<span class="code-comment"># Logo for dark backgrounds (dark mode, footers)</span>
<span class="code-variable">APP_LOGO_LIGHT</span>=<span class="code-string">/images/light_logo.png</span></code></pre>
                            </div>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Default</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">APP_LOGO_DARK</code></td>
                                            <td><code class="doc-inline-code">/images/dark_logo.png</code></td>
                                            <td>Logo displayed on light backgrounds</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">APP_LOGO_LIGHT</code></td>
                                            <td><code class="doc-inline-code">/images/light_logo.png</code></td>
                                            <td>Logo displayed on dark backgrounds</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Logo Guidelines</div>
                                <ul class="doc-list mt-2">
                                    <li>Place logo files in <code class="doc-inline-code">public/images/</code></li>
                                    <li>Recommended dimensions: 200px width, transparent background</li>
                                    <li>Supported formats: PNG, SVG</li>
                                    <li>The dark logo should have dark/black text (for light backgrounds)</li>
                                    <li>The light logo should have light/white text (for dark backgrounds)</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">Support Configuration</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Email address for user feedback (displayed in footer)</span>
<span class="code-variable">SUPPORT_EMAIL</span>=<span class="code-string">contact@eventschedule.com</span></code></pre>
                            </div>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Default</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">SUPPORT_EMAIL</code></td>
                                            <td><code class="doc-inline-code">contact@eventschedule.com</code></td>
                                            <td>Email address displayed in the footer for user suggestions and feedback</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">Pricing and Trial Configuration</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Free trial length in days for new Pro subscribers</span>
<span class="code-variable">TRIAL_DAYS</span>=<span class="code-value">365</span></code></pre>
                            </div>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Default</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">TRIAL_DAYS</code></td>
                                            <td><code class="doc-inline-code">365</code></td>
                                            <td>Number of days for free trial when new schedules are created or users subscribe to Pro</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">How Trials Work</div>
                                <ul class="doc-list mt-2">
                                    <li>When a new schedule is created in hosted mode, it gets Pro features for the configured trial period</li>
                                    <li>When users subscribe via Stripe, eligible users get a trial period before billing starts</li>
                                    <li>Prices are defined in your Stripe dashboard; the Price IDs are referenced via environment variables</li>
                                </ul>
                            </div>
                        </section>

                        <!-- DNS Configuration -->
                        <section id="dns" class="doc-section">
                            <h2 class="doc-heading">DNS Configuration</h2>
                            <p class="text-gray-300 mb-6">For SaaS mode to work, you need to configure wildcard DNS records.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">DNS Records</h3>
                            <p class="text-gray-300 mb-4">Add the following DNS records to your domain:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>DNS (A Records)</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># A record for main domain</span>
yourdomain.com.    A    YOUR_SERVER_IP

<span class="code-comment"># Wildcard A record for subdomains</span>
*.yourdomain.com.  A    YOUR_SERVER_IP</code></pre>
                            </div>

                            <p class="text-gray-300 mb-4">Or if using a CNAME:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>DNS (CNAME Records)</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># CNAME for main domain</span>
yourdomain.com.    CNAME    your-server.hosting.com.

<span class="code-comment"># Wildcard CNAME for subdomains</span>
*.yourdomain.com.  CNAME    your-server.hosting.com.</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">SSL Certificate</h3>
                            <p class="text-gray-300 mb-4">You'll need a wildcard SSL certificate that covers both the main domain and all subdomains:</p>
                            <ul class="doc-list">
                                <li>Certificate should cover: <code class="doc-inline-code">yourdomain.com</code> and <code class="doc-inline-code">*.yourdomain.com</code></li>
                                <li>Let's Encrypt supports wildcard certificates via DNS-01 challenge</li>
                                <li>Many hosting providers offer wildcard certificates</li>
                            </ul>
                        </section>

                        <!-- Web Server Configuration -->
                        <section id="webserver" class="doc-section">
                            <h2 class="doc-heading">Web Server Configuration</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Nginx Example</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>nginx.conf</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">server</span> {
    <span class="code-keyword">listen</span> <span class="code-value">443</span> ssl http2;
    <span class="code-keyword">server_name</span> yourdomain.com *.yourdomain.com;

    <span class="code-keyword">ssl_certificate</span> /path/to/wildcard.crt;
    <span class="code-keyword">ssl_certificate_key</span> /path/to/wildcard.key;

    <span class="code-keyword">root</span> /var/www/eventschedule/public;
    <span class="code-keyword">index</span> index.php;

    <span class="code-keyword">location</span> / {
        <span class="code-keyword">try_files</span> $uri $uri/ /index.php?$query_string;
    }

    <span class="code-keyword">location</span> ~ \.php$ {
        <span class="code-keyword">fastcgi_pass</span> unix:/var/run/php/php8.2-fpm.sock;
        <span class="code-keyword">fastcgi_param</span> SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        <span class="code-keyword">include</span> fastcgi_params;
    }
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">Apache Example</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>apache.conf</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">&lt;VirtualHost</span> *:443<span class="code-keyword">&gt;</span>
    <span class="code-keyword">ServerName</span> yourdomain.com
    <span class="code-keyword">ServerAlias</span> *.yourdomain.com

    <span class="code-keyword">DocumentRoot</span> /var/www/eventschedule/public

    <span class="code-keyword">SSLEngine</span> on
    <span class="code-keyword">SSLCertificateFile</span> /path/to/wildcard.crt
    <span class="code-keyword">SSLCertificateKeyFile</span> /path/to/wildcard.key

    <span class="code-keyword">&lt;Directory</span> /var/www/eventschedule/public<span class="code-keyword">&gt;</span>
        <span class="code-keyword">AllowOverride</span> All
        <span class="code-keyword">Require</span> all granted
    <span class="code-keyword">&lt;/Directory&gt;</span>
<span class="code-keyword">&lt;/VirtualHost&gt;</span></code></pre>
                            </div>
                        </section>

                        <!-- Stripe Subscription Setup -->
                        <section id="stripe" class="doc-section">
                            <h2 class="doc-heading">Stripe Subscription Setup (Pro Plans)</h2>
                            <p class="text-gray-300 mb-6">To enable paid Pro plans for your customers, configure Stripe subscription billing.</p>

                            <p class="text-gray-300 mb-6">See <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="text-blue-400 hover:text-blue-300 underline">Stripe Integration Documentation</a> for detailed Stripe configuration instructions.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Required Environment Variables</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Stripe Platform (for subscription billing)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_webhook_secret</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_monthly_price_id</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_yearly_price_id</span></code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">How Subscriptions Work</h3>
                            <ol class="doc-list doc-list-numbered">
                                <li>Customers create a schedule (gets a free plan by default)</li>
                                <li>Customers can upgrade to Pro from their schedule's admin page</li>
                                <li>Pro features are unlocked for that schedule</li>
                                <li>Subscriptions are managed per-schedule, not per-user</li>
                            </ol>
                        </section>

                        <!-- Complete Example Configuration -->
                        <section id="example" class="doc-section">
                            <h2 class="doc-heading">Complete Example Configuration</h2>
                            <p class="text-gray-300 mb-6">Here's a complete <code class="doc-inline-code">.env</code> configuration for a SaaS deployment:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Application</span>
<span class="code-variable">APP_NAME</span>=<span class="code-string">My Events Platform</span>
<span class="code-variable">APP_ENV</span>=<span class="code-string">production</span>
<span class="code-variable">APP_DEBUG</span>=<span class="code-value">false</span>
<span class="code-variable">APP_URL</span>=<span class="code-string">https://myevents.com</span>
<span class="code-variable">APP_MARKETING_URL</span>=<span class="code-string">https://myevents.com</span>

<span class="code-comment"># SaaS Mode</span>
<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span>

<span class="code-comment"># Branding</span>
<span class="code-variable">APP_LOGO_DARK</span>=<span class="code-string">/images/dark_logo.png</span>
<span class="code-variable">APP_LOGO_LIGHT</span>=<span class="code-string">/images/light_logo.png</span>
<span class="code-variable">SUPPORT_EMAIL</span>=<span class="code-string">support@myevents.com</span>

<span class="code-comment"># Trial Configuration</span>
<span class="code-variable">TRIAL_DAYS</span>=<span class="code-value">365</span>

<span class="code-comment"># Database</span>
<span class="code-variable">DB_CONNECTION</span>=<span class="code-string">mysql</span>
<span class="code-variable">DB_HOST</span>=<span class="code-string">127.0.0.1</span>
<span class="code-variable">DB_PORT</span>=<span class="code-value">3306</span>
<span class="code-variable">DB_DATABASE</span>=<span class="code-string">eventschedule</span>
<span class="code-variable">DB_USERNAME</span>=<span class="code-string">your_db_user</span>
<span class="code-variable">DB_PASSWORD</span>=<span class="code-string">your_db_password</span>

<span class="code-comment"># Session (important for subdomains)</span>
<span class="code-variable">SESSION_DRIVER</span>=<span class="code-string">database</span>
<span class="code-variable">SESSION_DOMAIN</span>=<span class="code-string">.myevents.com</span>

<span class="code-comment"># Mail</span>
<span class="code-variable">MAIL_MAILER</span>=<span class="code-string">smtp</span>
<span class="code-variable">MAIL_HOST</span>=<span class="code-string">smtp.mailgun.org</span>
<span class="code-variable">MAIL_PORT</span>=<span class="code-value">587</span>
<span class="code-variable">MAIL_USERNAME</span>=<span class="code-string">your_mail_user</span>
<span class="code-variable">MAIL_PASSWORD</span>=<span class="code-string">your_mail_password</span>
<span class="code-variable">MAIL_FROM_ADDRESS</span>=<span class="code-string">hello@myevents.com</span>
<span class="code-variable">MAIL_FROM_NAME</span>=<span class="code-string">"${APP_NAME}"</span>

<span class="code-comment"># Stripe Platform (optional, for Pro subscriptions)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_...</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_...</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_...</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_...</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_...</span></code></pre>
                            </div>

                            <div class="doc-callout doc-callout-warning mt-6">
                                <div class="doc-callout-title">Important</div>
                                <p>Set <code class="doc-inline-code">SESSION_DOMAIN</code> to <code class="doc-inline-code">.yourdomain.com</code> (with leading dot) to allow session sharing across subdomains.</p>
                            </div>
                        </section>

                        <!-- Verification Steps -->
                        <section id="verification" class="doc-section">
                            <h2 class="doc-heading">Verification Steps</h2>
                            <p class="text-gray-300 mb-6">After completing the configuration, verify your setup:</p>

                            <h3 class="text-lg font-semibold text-white mb-4">1. Test Main Domain</h3>
                            <p class="text-gray-300 mb-6">Visit <code class="doc-inline-code">https://yourdomain.com</code> - you should see the marketing/landing page.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">2. Test Subdomain Routing</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Create a new account and schedule</li>
                                <li>Note the schedule's subdomain (e.g., <code class="doc-inline-code">my-schedule</code>)</li>
                                <li>Visit <code class="doc-inline-code">https://my-schedule.yourdomain.com</code></li>
                                <li>The schedule's public page should load</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">3. Test SSL Certificate</h3>
                            <p class="text-gray-300 mb-2">Verify SSL works for both:</p>
                            <ul class="doc-list mb-6">
                                <li>Main domain: <code class="doc-inline-code">https://yourdomain.com</code></li>
                                <li>Any subdomain: <code class="doc-inline-code">https://test.yourdomain.com</code></li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">4. Test Subscription Flow (if configured)</h3>
                            <ol class="doc-list doc-list-numbered">
                                <li>Go to a schedule's admin page</li>
                                <li>Click "Upgrade to Pro"</li>
                                <li>Complete checkout with a test card (<code class="doc-inline-code">4242 4242 4242 4242</code>)</li>
                                <li>Verify Pro features are enabled</li>
                            </ol>
                        </section>

                        <!-- Demo Mode -->
                        <section id="demo" class="doc-section">
                            <h2 class="doc-heading">Demo Mode (Optional)</h2>
                            <p class="text-gray-300 mb-6">Demo mode lets potential customers try your platform without signing up. Visitors to <code class="doc-inline-code">demo.yourdomain.com</code> are automatically logged in to a demo account with sample data.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">How It Works</h3>
                            <ul class="doc-list mb-6">
                                <li>A special subdomain (<code class="doc-inline-code">demo.yourdomain.com</code>) triggers auto-login</li>
                                <li>Visitors are redirected to a pre-populated schedule with sample events, tickets, groups, and sales</li>
                                <li>The demo schedule has Pro features enabled</li>
                                <li>Demo data can be reset periodically to stay fresh</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">Setting Up Demo Mode</h3>
                            <p class="text-gray-300 mb-4">Run the setup command to create the demo account and sample data:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>php artisan app:setup-demo</code></pre>
                            </div>

                            <p class="text-gray-300 mb-6 mt-4">This creates a demo user, a schedule called <code class="doc-inline-code">thenightowls</code>, and populates it with sample events, tickets, groups, and sales data.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Resetting Demo Data</h3>
                            <p class="text-gray-300 mb-4">Running the setup command again will automatically reset the demo data:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>php artisan app:setup-demo</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4 mt-8">Scheduling Automatic Resets (Optional)</h3>
                            <p class="text-gray-300 mb-4">To keep demo data fresh, you can schedule hourly resets by adding this to your cron:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>crontab</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>0 * * * * cd /path/to/eventschedule && php artisan app:setup-demo >> /dev/null 2>&1</code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">Note</div>
                                <p>Demo mode only works in hosted mode (<code class="doc-inline-code">IS_HOSTED=true</code>) since it relies on subdomain routing.</p>
                            </div>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">Troubleshooting</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Common Issues</h3>

                            <div class="space-y-4 mb-8">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Subdomains show 404 or wrong page</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check that <code class="doc-inline-code">IS_HOSTED=true</code> is set</li>
                                        <li>Verify wildcard DNS is configured correctly</li>
                                        <li>Ensure web server is configured for wildcard subdomains</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"Session domain mismatch" or login issues across subdomains</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Set <code class="doc-inline-code">SESSION_DOMAIN=.yourdomain.com</code> (with leading dot)</li>
                                        <li>Clear browser cookies and try again</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">SSL certificate errors on subdomains</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify wildcard certificate covers <code class="doc-inline-code">*.yourdomain.com</code></li>
                                        <li>Check certificate is properly installed in web server</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Logo not displaying</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify logo files exist in <code class="doc-inline-code">public/images/</code></li>
                                        <li>Check file permissions are readable</li>
                                        <li>Ensure paths in <code class="doc-inline-code">.env</code> match actual file locations</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Logs</h3>
                            <p class="text-gray-300 mb-4">Check the application logs for errors:</p>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code>tail -f storage/logs/laravel.log</code></pre>
                            </div>
                        </section>

                        <!-- Security Considerations -->
                        <section id="security" class="doc-section">
                            <h2 class="doc-heading">Security Considerations</h2>
                            <ol class="doc-list doc-list-numbered">
                                <li><span class="font-semibold text-white">Environment File:</span> Never expose <code class="doc-inline-code">.env</code> file publicly</li>
                                <li><span class="font-semibold text-white">HTTPS Required:</span> Always use HTTPS in production</li>
                                <li><span class="font-semibold text-white">API Keys:</span> Keep all API keys and secrets secure</li>
                                <li><span class="font-semibold text-white">Database:</span> Use strong database passwords and restrict access</li>
                                <li><span class="font-semibold text-white">File Permissions:</span> Ensure proper file permissions on the server</li>
                            </ol>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
