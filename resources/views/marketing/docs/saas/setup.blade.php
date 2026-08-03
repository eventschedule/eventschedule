<x-docs-page
    key="saas/setup"
    description="Learn how to configure Event Schedule for SaaS deployment with subdomain-based multi-tenant routing, custom branding, and Stripe subscriptions."
    lede="Configure Event Schedule for SaaS (Software as a Service) deployment, where you host the platform for multiple customers using subdomains."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-link href="#environment">Environment Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#dns">DNS Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#webserver">Web Server Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#stripe">Stripe Subscription Setup</x-doc-nav-link>
        <x-doc-nav-link href="#example">Complete Example</x-doc-nav-link>
        <x-doc-nav-link href="#verification">Verification Steps</x-doc-nav-link>
        <x-doc-nav-link href="#demo">Demo Mode</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#support-chat">Support Chat</x-doc-nav-link>
        <x-doc-nav-link href="#translations">Custom translations</x-doc-nav-link>
        <x-doc-nav-link href="#custom-links">Custom dashboard links</x-doc-nav-link>
        <x-doc-nav-link href="#related">Related Documentation</x-doc-nav-link>
        <x-doc-nav-link href="#security">Security Considerations</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports two deployment modes:</p>

        <div class="doc-table-wrap">
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
                        <td><span class="font-semibold text-gray-900 dark:text-white">Selfhosted</span></td>
                        <td>Path-based <code class="doc-inline-code">/schedule-name/...</code></td>
                        <td>Single organization or personal use</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">SaaS/Hosted</span></td>
                        <td>Subdomain-based <code class="doc-inline-code">schedule-name.yourdomain.com</code></td>
                        <td>Multi-tenant platform for multiple customers</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">In SaaS mode each customer schedule gets its own subdomain, and signing in, the admin portal and billing all live on one shared <code class="doc-inline-code">app</code> subdomain. A schedule on an Enterprise plan can additionally be served from the customer's own domain; see <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="doc-link">Custom Domains</a>.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your platform does not serve the Event Schedule marketing site</div>
            <p>The marketing pages (home, features, pricing, this user guide) are registered only when
            <code class="doc-inline-code">IS_NEXUS=true</code>, which identifies the one upstream install that
            receives federated events and shared translations. Leave it unset on your own platform. Your root
            domain then redirects visitors to the sign-in page, and you point
            <code class="doc-inline-code">APP_MARKETING_URL</code> at whatever marketing site you run yourself.</p>
        </div>
    </section>

    <!-- Prerequisites -->
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li>A completed base installation of Event Schedule, including MySQL and the <code class="doc-inline-code">schedule:run</code> cron entry (see <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a>)</li>
            <li>A domain name with DNS access</li>
            <li>Ability to configure wildcard SSL certificates</li>
            <li>Web server configured to handle wildcard subdomains (Apache or Nginx)</li>
            <li>A working mail transport: tenant invitations, ticket confirmations, subscription receipts and support notifications all send from this install</li>
        </ol>
    </section>

    <!-- Environment Configuration -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Environment Configuration
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add the following variables to your <code class="doc-inline-code">.env</code> file to enable SaaS mode:</p>

        <h3 class="doc-subheading">Core SaaS Settings</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Enable SaaS mode with subdomain routing</span>
<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span>

<span class="code-comment"># Sender name on outgoing email, via MAIL_FROM_NAME="${APP_NAME}"</span>
<span class="code-variable">APP_NAME</span>=<span class="code-string">Your Platform Name</span>

<span class="code-comment"># Main application URL (use app subdomain)</span>
<span class="code-variable">APP_URL</span>=<span class="code-string">https://app.yourdomain.com</span>

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
                        <td>Reaches the app only through the <code class="doc-inline-code">MAIL_FROM_NAME="${APP_NAME}"</code> reference in <code class="doc-inline-code">.env.example</code>, so it sets the sender name on outgoing email. It does <span class="font-semibold text-gray-900 dark:text-white">not</span> rename the product in the interface: admin and marketing page titles are literal, and <code class="doc-inline-code">config('app.name')</code> is a fixed <code class="doc-inline-code">Event Schedule</code> string in <code class="doc-inline-code">config/app.php</code>. Public schedule pages are already unbranded, since their title carries the schedule's own name. Rename in-app wording with <a href="#translations" class="doc-link">custom translations</a> instead.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_URL</code></td>
                        <td>-</td>
                        <td>Application URL. Set to the <code class="doc-inline-code">app</code> subdomain (e.g. <code class="doc-inline-code">https://app.yourdomain.com</code>). The base domain is derived by stripping a leading <code class="doc-inline-code">app.</code>, <code class="doc-inline-code">www.</code>, <code class="doc-inline-code">blog.</code> or <code class="doc-inline-code">demo.</code>, and the <code class="doc-inline-code">blog</code> and <code class="doc-inline-code">demo</code> subdomains are then built back from it automatically.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_MARKETING_URL</code></td>
                        <td><code class="doc-inline-code">https://eventschedule.com</code></td>
                        <td>Your own marketing site. This is where the footer strip on your free tier's public pages sends visitors, so point it at your site rather than leaving the default.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">IS_NEXUS</code></td>
                        <td><code class="doc-inline-code">false</code></td>
                        <td>Leave this off. It marks the single upstream install that hosts the Event Schedule marketing site and receives federated events and shared translation suggestions. Turning it on also changes the default proxy trust and disables the in-app updater.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Branding Customization</h3>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One credit a page</div>
            <p>Your app name, logos and domain make the platform yours, and your free tier's footer
            strip points at your <code class="doc-inline-code">APP_MARKETING_URL</code> rather than
            ours. One thing is not yours to repoint: a small "Event Schedule" chip in the corner of
            the public pages of every customer you charge. It is the
            attribution the <a href="https://github.com/eventschedule/eventschedule/blob/main/LICENSE" target="_blank" rel="noopener" class="doc-link">Attribution Assurance License</a>
            asks for in return for the software, so it links to eventschedule.com and
            <code class="doc-inline-code">APP_MARKETING_URL</code> does not change it. A free schedule
            shows your footer strip instead of the chip, so no page carries two credits.</p>
        </div>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Logo for light backgrounds (header, emails)</span>
<span class="code-variable">APP_LOGO_DARK</span>=<span class="code-string">/images/dark_logo.png</span>

<span class="code-comment"># Logo for dark backgrounds (dark mode, footers)</span>
<span class="code-variable">APP_LOGO_LIGHT</span>=<span class="code-string">/images/light_logo.png</span></code></pre>
        </div>

        <div class="doc-table-wrap">
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

        <h3 class="doc-subheading">Support Configuration</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Email address for user feedback (displayed in footer)</span>
<span class="code-variable">SUPPORT_EMAIL</span>=<span class="code-string">contact@eventschedule.com</span></code></pre>
        </div>

        <div class="doc-table-wrap">
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
                        <td>Shown at the bottom of the admin sidebar as the "questions or suggestions" address, and used as the Reply-To on the notices sent when an account, schedule or event is deleted. Change it or your customers will write to us.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Pricing and Trial Configuration</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Free trial length in days for new Pro subscribers</span>
<span class="code-variable">TRIAL_DAYS</span>=<span class="code-value">7</span></code></pre>
        </div>

        <div class="doc-table-wrap">
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
                        <td><code class="doc-inline-code">7</code></td>
                        <td>Length of the Stripe trial granted when a schedule subscribes for the first time. The shipped <code class="doc-inline-code">.env.example</code> sets <code class="doc-inline-code">365</code>, so set it deliberately.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">How Trials Work</div>
            <ul class="doc-list mt-2">
                <li>A new schedule starts on the <span class="font-semibold text-gray-900 dark:text-white">Free</span> plan. Nothing grants it Pro automatically, so the free tier is what every customer sees first</li>
                <li>The trial is applied at subscribe time: a schedule that has never had a plan or a subscription gets <code class="doc-inline-code">TRIAL_DAYS</code> before Stripe takes the first payment, and the subscribe page shows a free-trial badge</li>
                <li>A schedule carrying a legacy expiry date instead gets its remaining days as the trial length</li>
                <li>Amounts are defined by the Price objects in your Stripe dashboard; the app only stores the Price IDs, plus separate display amounts (see <a href="#stripe" class="doc-link">Stripe Subscription Setup</a>)</li>
            </ul>
        </div>

        <h3 id="push-notifications" class="doc-subheading">Push Notifications (Optional)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Event Schedule can send web push notifications that mirror its email notifications using <a href="https://onesignal.com" target="_blank" rel="noopener noreferrer" class="doc-link">OneSignal</a>. This is a Pro feature and is <strong>off by default</strong>: with no configuration, no push SDK loads and no calls are made to OneSignal. To enable it platform-wide, create a OneSignal app (Web platform) and set:</p>
        <pre class="doc-code-block"><code>ONESIGNAL_APP_ID=your-onesignal-app-id
ONESIGNAL_REST_API_KEY=your-onesignal-rest-api-key</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once both values are set, a <strong>Push notifications</strong> panel appears on each schedule's <strong>Settings &rarr; Notifications</strong> tab, where the owner enables push per device and can send a test. Sending is gated on the schedule being Pro or Enterprise, and the demo schedule never receives push. One OneSignal app serves the whole platform; tenants are segmented automatically. Add <code class="doc-inline-code">ONESIGNAL_SAFARI_WEB_ID</code> only if you need legacy macOS Safari support.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Note that enabling push loads the OneSignal SDK from their CDN and sends notification data to OneSignal, and that Apple iOS only supports web push for sites added to the home screen (iOS 16.4+).</p>

        <h3 id="reverse-proxy" class="doc-subheading">Running Behind a Reverse Proxy</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A multi-tenant install almost always sits behind a reverse proxy or CDN (Nginx, Apache, Cloudflare, or a control panel such as HestiaCP). Tell Event Schedule which proxies to trust so it reads the <code class="doc-inline-code">X-Forwarded-Proto</code> and <code class="doc-inline-code">X-Forwarded-For</code> headers those proxies set:</p>
        <pre class="doc-code-block"><code>TRUSTED_PROXIES=*</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use <code class="doc-inline-code">*</code> to trust any proxy, or a comma-separated list of proxy IPs or CIDR ranges (for example <code class="doc-inline-code">10.0.0.0/8,192.168.1.1</code>) when the origin server is reachable directly from the internet. Left unset, your platform trusts no proxies at all: the application then treats every request as plain HTTP even when the browser is on HTTPS, which can produce redirect loops on tenant subdomains, and it records the proxy's IP address as the visitor's IP in analytics and rate limiting.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The setting deliberately lives in <code class="doc-inline-code">config/trustedproxy.php</code> rather than in application bootstrap, so it survives <code class="doc-inline-code">php artisan config:cache</code>. Re-run that command after changing the value.</p>
    </section>

    <!-- DNS Configuration -->
    <section id="dns" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            DNS Configuration
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">For SaaS mode to work, you need to configure wildcard DNS records.</p>

        <h3 class="doc-subheading">DNS Records</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following DNS records to your domain:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>DNS (A Records)</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># A record for main domain</span>
yourdomain.com.    A    YOUR_SERVER_IP

<span class="code-comment"># Wildcard A record for subdomains</span>
*.yourdomain.com.  A    YOUR_SERVER_IP</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Or if using a CNAME:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>DNS (CNAME Records)</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># CNAME for main domain</span>
yourdomain.com.    CNAME    your-server.hosting.com.

<span class="code-comment"># Wildcard CNAME for subdomains</span>
*.yourdomain.com.  CNAME    your-server.hosting.com.</code></pre>
        </div>

        <h3 class="doc-subheading">SSL Certificate</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You'll need a wildcard SSL certificate that covers both the main domain and all subdomains:</p>
        <ul class="doc-list">
            <li>Certificate should cover: <code class="doc-inline-code">yourdomain.com</code> and <code class="doc-inline-code">*.yourdomain.com</code></li>
            <li>Let's Encrypt supports wildcard certificates via DNS-01 challenge</li>
            <li>Many hosting providers offer wildcard certificates</li>
        </ul>
    </section>

    <!-- Web Server Configuration -->
    <section id="webserver" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z" />
            </svg>
            Web Server Configuration
        </h2>

        <h3 class="doc-subheading">Nginx Example</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>nginx.conf</span>
                <button class="doc-copy-btn">Copy</button>
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

        <h3 class="doc-subheading">Apache Example</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>apache.conf</span>
                <button class="doc-copy-btn">Copy</button>
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
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Stripe Subscription Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">To sell paid plans to your customers, configure Stripe subscription billing. The subscription charges are made on your own Stripe account. This is separate from ticket payments, which are charged on each schedule owner's connected account with no platform fee.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">See the <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Stripe integration documentation</a> for step-by-step key, webhook and Connect instructions.</p>

        <h3 class="doc-subheading">Required Environment Variables</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Stripe Platform (for subscription billing)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_webhook_secret</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_monthly_price_id</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_yearly_price_id</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Those five cover the Pro tier. Selling Enterprise, and showing the right numbers in the interface, needs four more:</p>

        <div class="doc-table-wrap">
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
                        <td><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY</code></td>
                        <td>-</td>
                        <td>Stripe Price ID for monthly Enterprise. The "Upgrade to Enterprise" button is hidden until both Enterprise Price IDs are set.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY</code></td>
                        <td>-</td>
                        <td>Stripe Price ID for yearly Enterprise</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_PRICE_MONTHLY_AMOUNT</code><br><code class="doc-inline-code">STRIPE_PRICE_YEARLY_AMOUNT</code></td>
                        <td><code class="doc-inline-code">5</code> / <code class="doc-inline-code">50</code></td>
                        <td>Display-only Pro amounts shown on the subscribe page, the Plan tab and upgrade prompts</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT</code><br><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT</code></td>
                        <td><code class="doc-inline-code">15</code> / <code class="doc-inline-code">150</code></td>
                        <td>Display-only Enterprise amounts</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The amounts are labels, not prices</div>
            <p>The <code class="doc-inline-code">*_AMOUNT</code> variables only decide what the interface prints. What a
            customer is actually charged comes from the Stripe Price the matching Price ID points at. Set both, and keep
            them in step, or your platform will advertise one figure and bill another.</p>
        </div>

        <h3 class="doc-subheading">Webhook Endpoint</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Subscriptions are kept in sync by a webhook that is separate from the ticket-payment one. In your Stripe dashboard add an endpoint pointing at <code class="doc-inline-code">https://app.yourdomain.com/stripe/subscription-webhook</code> and copy its signing secret into <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>. It is this webhook that downgrades a schedule to Free when its subscription is deleted, and that raises the payment-failed notice, so without it a cancellation in Stripe never reaches your platform.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Do not leave the signing secret blank</div>
            <p>Signature checking is only switched on when <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>
            has a value. Leave it empty and the endpoint stays open, accepting unsigned requests that could downgrade or
            upgrade any schedule on your platform. Set it as soon as you create the endpoint.</p>
        </div>

        <h3 class="doc-subheading">How Subscriptions Work</h3>
        <ol class="doc-list doc-list-numbered">
            <li>A customer creates a schedule. It starts on the Free plan</li>
            <li>They open the schedule's admin portal and go to the <span class="font-semibold text-gray-900 dark:text-white">Plan</span> tab, which shows the current plan, status and the ticket, newsletter and photo allowances</li>
            <li>They click <span class="font-semibold text-gray-900 dark:text-white">Upgrade to Pro</span> and pay. The button only appears once <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> is set</li>
            <li>Pro features unlock for that schedule, and the free-tier footer strip and ad slot come off its public pages</li>
            <li>An active Pro subscriber can then switch to Enterprise, or between monthly and yearly, from the same tab. <span class="font-semibold text-gray-900 dark:text-white">Manage Subscription</span> opens the Stripe billing portal</li>
            <li>Subscriptions are per schedule, not per user: a customer with three schedules pays for each one they upgrade</li>
        </ol>
    </section>

    <!-- Complete Example Configuration -->
    <section id="example" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Complete Example Configuration
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Here's a complete <code class="doc-inline-code">.env</code> configuration for a SaaS deployment:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Application</span>
<span class="code-variable">APP_NAME</span>=<span class="code-string">My Events Platform</span>
<span class="code-variable">APP_ENV</span>=<span class="code-string">production</span>
<span class="code-variable">APP_DEBUG</span>=<span class="code-value">false</span>
<span class="code-variable">APP_URL</span>=<span class="code-string">https://app.myevents.com</span>
<span class="code-variable">APP_MARKETING_URL</span>=<span class="code-string">https://myevents.com</span>

<span class="code-comment"># SaaS Mode</span>
<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span>

<span class="code-comment"># Branding</span>
<span class="code-variable">APP_LOGO_DARK</span>=<span class="code-string">/images/dark_logo.png</span>
<span class="code-variable">APP_LOGO_LIGHT</span>=<span class="code-string">/images/light_logo.png</span>
<span class="code-variable">SUPPORT_EMAIL</span>=<span class="code-string">support@myevents.com</span>

<span class="code-comment"># Trial Configuration</span>
<span class="code-variable">TRIAL_DAYS</span>=<span class="code-value">7</span>

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
            <p>Set <code class="doc-inline-code">SESSION_DOMAIN</code> to <code class="doc-inline-code">.yourdomain.com</code> (with leading dot) to allow session sharing across subdomains. If left unset, hosted mode automatically defaults it to your <code class="doc-inline-code">APP_URL</code> base domain; setting it explicitly takes precedence.</p>
            <p class="mt-2">Requests arriving on a customer's own domain are the exception: the session domain is cleared for those requests only, so the cookie is scoped to that origin instead of one the browser would reject. That is also why signing in always happens on your <code class="doc-inline-code">app</code> subdomain rather than on a custom domain.</p>
        </div>
    </section>

    <!-- Verification Steps -->
    <section id="verification" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Verification Steps
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">After completing the configuration, verify your setup:</p>

        <h3 class="doc-subheading">1. Test the App Subdomain</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Visit <code class="doc-inline-code">https://app.yourdomain.com</code>. You should reach the sign-in page, and be able to register an account.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The bare root domain redirects to that same sign-in page. That is the expected result: your platform does not serve the Event Schedule marketing pages, so put your own site on the root domain (or on a separate host) and point <code class="doc-inline-code">APP_MARKETING_URL</code> at it.</p>

        <h3 class="doc-subheading">2. Test Subdomain Routing</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Create a new account and schedule</li>
            <li>Note the schedule's subdomain (e.g. <code class="doc-inline-code">my-schedule</code>)</li>
            <li>Visit <code class="doc-inline-code">https://my-schedule.yourdomain.com</code></li>
            <li>The schedule's public page should load, and stay signed in when you move back to <code class="doc-inline-code">app.yourdomain.com</code></li>
        </ol>

        <h3 class="doc-subheading">3. Test SSL Certificate</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Verify SSL works for both:</p>
        <ul class="doc-list mb-6">
            <li>Main domain: <code class="doc-inline-code">https://yourdomain.com</code></li>
            <li>Any subdomain: <code class="doc-inline-code">https://test.yourdomain.com</code></li>
        </ul>

        <h3 class="doc-subheading">4. Test Subscription Flow (if configured)</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Open a schedule's admin portal and select the <span class="font-semibold text-gray-900 dark:text-white">Plan</span> tab</li>
            <li>Click <span class="font-semibold text-gray-900 dark:text-white">Upgrade to Pro</span>. If the button is missing, <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> is not set</li>
            <li>Complete checkout with the test card <code class="doc-inline-code">4242 4242 4242 4242</code>, which only works while your keys are the <code class="doc-inline-code">sk_test_</code> / <code class="doc-inline-code">pk_test_</code> pair</li>
            <li>Confirm the Plan tab now reports Pro, and that the free-tier footer strip has gone from the schedule's public page</li>
            <li>Cancel from the Stripe dashboard and confirm the Plan tab picks it up, which proves the subscription webhook is wired correctly</li>
        </ol>
    </section>

    <!-- Demo Mode -->
    <section id="demo" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Demo Mode (Optional)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Demo mode lets potential customers try your platform without signing up. Visitors to <code class="doc-inline-code">demo.yourdomain.com</code> are automatically logged in to a demo account with sample data.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ul class="doc-list mb-6">
            <li>A request to the <code class="doc-inline-code">demo</code> subdomain signs the visitor in as the demo user, with no password prompt</li>
            <li>They land in the <span class="font-semibold text-gray-900 dark:text-white">admin portal</span> for the demo schedule, on its Schedule tab, so what they try is the real product rather than a public page</li>
            <li>The demo interface follows the visitor's browser language, chosen from your supported languages on first visit</li>
            <li>A visitor already signed in as a real user is bounced back to your app rather than switched into the demo</li>
            <li>Demo data can be reset periodically to stay fresh</li>
        </ul>

        <h3 class="doc-subheading">Setting Up Demo Mode</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Run the setup command to create the demo account and sample data:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan app:setup-demo</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4 mt-4">This creates the demo user and a curator schedule on the <code class="doc-inline-code">simpsons</code> subdomain, then populates a small Springfield-themed network around it: talent and venue schedules, sub-schedules, events with ticket types, followed schedules, sample ticket purchases and analytics history.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Two things to check before you run it</div>
            <ul class="doc-list mt-2">
                <li>The demo account is created with the fixed address <code class="doc-inline-code">contact@eventschedule.com</code>. If that address already belongs to a real account on your platform, that account becomes the demo account</li>
                <li>The demo schedule is created on the Free plan like any other, so Pro-only screens stay locked and its public pages carry your free-tier footer. Grant it a plan from <span class="font-semibold text-gray-900 dark:text-white">/admin &rarr; Schedules</span> if you want to show off paid features</li>
            </ul>
        </div>

        <h3 class="doc-subheading">Resetting Demo Data</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Running the setup command again will automatically reset the demo data:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan app:setup-demo</code></pre>
        </div>

        <h3 class="doc-subheading">Scheduling Automatic Resets (Optional)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To keep demo data fresh, you can schedule hourly resets by adding this to your cron:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>0 * * * * cd /path/to/eventschedule && php artisan app:setup-demo >> /dev/null 2>&1</code></pre>
        </div>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Note</div>
            <p>Demo mode only works in hosted mode (<code class="doc-inline-code">IS_HOSTED=true</code>) since it relies on subdomain routing. The setup command refuses to run otherwise, and the auto-login middleware stays inert, so there is nothing to undo on a selfhosted install.</p>
        </div>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">Common Issues</h3>

        <div class="space-y-4 mb-8">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Subdomains show 404 or wrong page</h4>
                <ul class="doc-list text-sm">
                    <li>Check that <code class="doc-inline-code">IS_HOSTED=true</code> is set</li>
                    <li>Verify wildcard DNS is configured correctly</li>
                    <li>Ensure web server is configured for wildcard subdomains</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Session domain mismatch" or login issues across subdomains</h4>
                <ul class="doc-list text-sm">
                    <li>Set <code class="doc-inline-code">SESSION_DOMAIN=.yourdomain.com</code> (with leading dot). If unset, hosted mode defaults it to the <code class="doc-inline-code">APP_URL</code> base domain</li>
                    <li>Make sure <code class="doc-inline-code">APP_URL</code> is set to your app subdomain (e.g. <code class="doc-inline-code">https://app.yourdomain.com</code>)</li>
                    <li>Clear browser cookies and try again</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Redirect loop, or every visitor logged with the same IP address</h4>
                <ul class="doc-list text-sm">
                    <li>Set <code class="doc-inline-code">TRUSTED_PROXIES</code>. Left unset, your platform trusts no proxies and reads HTTPS requests as HTTP (see <a href="#reverse-proxy" class="doc-link">Running Behind a Reverse Proxy</a>)</li>
                    <li>Re-run <code class="doc-inline-code">php artisan config:cache</code> if you have cached your configuration</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">The root domain shows the sign-in page instead of a landing page</h4>
                <ul class="doc-list text-sm">
                    <li>This is expected. Marketing pages are only served when <code class="doc-inline-code">IS_NEXUS=true</code>, which is not a setting for your platform</li>
                    <li>Host your own marketing site and point <code class="doc-inline-code">APP_MARKETING_URL</code> at it</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">SSL certificate errors on subdomains</h4>
                <ul class="doc-list text-sm">
                    <li>Verify wildcard certificate covers <code class="doc-inline-code">*.yourdomain.com</code></li>
                    <li>Check certificate is properly installed in web server</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Logo not displaying</h4>
                <ul class="doc-list text-sm">
                    <li>Verify logo files exist in <code class="doc-inline-code">public/images/</code></li>
                    <li>Check file permissions are readable</li>
                    <li>Ensure paths in <code class="doc-inline-code">.env</code> match actual file locations</li>
                </ul>
            </div>
        </div>

        <h3 class="doc-subheading">Logs</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Check the application logs for errors:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>tail -f storage/logs/laravel.log</code></pre>
        </div>
    </section>

    <!-- Support Chat -->
    <section id="support-chat" class="doc-section">
        <h2 class="doc-heading">
            <svg class="inline-block w-7 h-7 me-2 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Support Chat
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule includes a built-in chat system that lets your customers message you for support without leaving the admin portal. It needs no configuration and is present on every hosted install; on a selfhosted install neither the widget nor the admin screen exists. Each customer has one running conversation with you, which reopens if they write again after you have closed it.</p>

        <h3 class="doc-subheading">For Your Customers</h3>
        <ul class="doc-list">
            <li><span class="font-semibold text-gray-900 dark:text-white">Chat widget:</span> A floating chat bubble in the bottom corner of the screen for signed-in users</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Availability indicator:</span> A green dot on the bubble while you are marked available</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Sidebar button:</span> A chat icon next to the Help link in the admin sidebar opens the same panel, with a red badge for unread replies</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Message limit:</span> Up to 2,000 characters per message, in both directions. Any HTML is stripped before the message is stored</li>
        </ul>

        <h3 class="doc-subheading">For You, the Platform Admin</h3>
        <ul class="doc-list">
            <li><span class="font-semibold text-gray-900 dark:text-white">Admin panel:</span> Manage conversations from <span class="font-semibold text-gray-900 dark:text-white">System &rarr; Support</span> in the admin panel at <code class="doc-inline-code">/admin</code></li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Availability toggle:</span> Switch yourself online to show the green dot. It lapses on its own after four hours, so you never leave it on overnight by accident</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Conversations list:</span> Every conversation, with unread badges, and a matching badge on the System menu</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Replying:</span> Open a conversation to read the history and reply</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Closing conversations:</span> Close resolved conversations to keep the list short</li>
        </ul>

        <h3 class="doc-subheading">Who Gets Notified</h3>
        <ul class="doc-list">
            <li>Every customer message emails you, whether or not you are marked available, and sends a push notification if OneSignal is configured</li>
            <li>Your reply emails the customer only when they are not currently in the chat, so an active back-and-forth does not fill their inbox</li>
            <li>Notifications go to the first account flagged as a platform admin, so keep one dedicated admin account with a monitored address</li>
        </ul>
    </section>

    <!-- Custom translations -->
    <section id="translations" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Custom translations
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Rename built-in UI terms to match your customers' vocabulary (for example "Talent" to "Artist", or "Curator" to "Event Planner") without your changes being wiped out by <code class="doc-inline-code">php artisan app:update</code>. Overrides apply globally across every tenant on your platform.</p>

        <h3 class="doc-subheading">The Easy Way: The Translation Manager</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Sign in as a platform admin and open <span class="font-semibold text-gray-900 dark:text-white">System &rarr; Translations</span> in the admin panel. Search for a phrase, edit it for the locale you want, and save. The database is the source of truth: each save is stored as an override and republished to a file on disk, so nothing is lost on the next upgrade. Reverting an override restores the bundled string.</p>

        <h3 class="doc-subheading">The Manual Way: Override Files</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You can also drop a PHP file in:</p>
        <pre class="rounded-xl bg-gray-100 dark:bg-[#1A1A1A] p-4 text-sm overflow-x-auto"><code>storage/app/lang/{locale}/{file}.php</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The three files you can override are <code class="doc-inline-code">messages.php</code> (UI strings), <code class="doc-inline-code">accessibility.php</code>, and <code class="doc-inline-code">marketing.php</code>. List the keys you want to change and nothing else; the bundled translations fill in the rest:</p>
        <pre class="rounded-xl bg-gray-100 dark:bg-[#1A1A1A] p-4 text-sm overflow-x-auto"><code>&lt;?php
// storage/app/lang/en/messages.php
return [
'talent' =&gt; 'Artist',
'talents' =&gt; 'Artists',
'curator' =&gt; 'Event Planner',
'curators' =&gt; 'Event Planners',
];</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Create one directory per locale you want to override (<code class="doc-inline-code">en</code>, <code class="doc-inline-code">es</code>, <code class="doc-inline-code">fr</code>, &hellip;). The full list of supported locales lives in <code class="doc-inline-code">config/app.php</code> under <code class="doc-inline-code">supported_languages</code>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A hand-written file for one of those three managed groups is adopted into the database the next time the overrides are republished, after which the file is regenerated from the database. Keep that in mind if you edit both by hand and through the admin panel, and keep nested array values in their own group file (<code class="doc-inline-code">validation.php</code>, <code class="doc-inline-code">auth.php</code> or a custom group), which the loader honours and never rewrites.</p>

        <h3 class="doc-subheading">Rebuilding and Moving Servers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The files are server-local derived state, so rebuild them from the database after restoring a backup or cloning the app to a new machine:</p>
        <pre class="doc-code-block"><code>php artisan translations:publish</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Run it on each web server, and restart your queue workers afterwards so long-running processes pick up the new strings. If you run several servers behind a load balancer, set <code class="doc-inline-code">LANG_OVERRIDES_PATH</code> to a shared volume instead and publish once. A relative value resolves from the application root; an absolute one is used as given.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why this works</div>
            <p>Changes apply on the next request, with no cache clear required. <code class="doc-inline-code">storage/app/</code> is gitignored, so your overrides survive <code class="doc-inline-code">php artisan app:update</code>, <code class="doc-inline-code">git pull</code>, and fresh checkouts.</p>
        </div>
    </section>

    <!-- Custom dashboard links -->
    <section id="custom-links" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Custom dashboard links
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add up to three custom links to the admin sidebar (for example a support site, community forum, or status page). On a SaaS deployment these links are <span class="font-semibold text-gray-900 dark:text-white">platform-wide</span>: every admin on every tenant sees them, just below the <span class="font-semibold text-gray-900 dark:text-white">Newsletters</span> link, and they open in a new tab.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Set the following variables in your <code class="doc-inline-code">.env</code> file. A link only appears when <span class="font-semibold text-gray-900 dark:text-white">both</span> its title and URL are filled in, so you can configure one, two, or three links:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>CUSTOM_LINK_1_TITLE=<span class="code-string">"Help Center"</span>
CUSTOM_LINK_1_URL=<span class="code-string">"https://help.example.com"</span>
CUSTOM_LINK_2_TITLE=<span class="code-string">"Status"</span>
CUSTOM_LINK_2_URL=<span class="code-string">"https://status.example.com"</span>
CUSTOM_LINK_3_TITLE=
CUSTOM_LINK_3_URL=</code></pre>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Reload cached config</div>
            <p>If you have run <code class="doc-inline-code">php artisan config:cache</code>, re-run it (or <code class="doc-inline-code">php artisan config:clear</code>) after editing <code class="doc-inline-code">.env</code> so the new links take effect.</p>
        </div>
    </section>

    <!-- Related Documentation -->
    <section id="related" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            Related Documentation
        </h2>
        <ul class="doc-list">
            <li><x-link href="/docs/saas/custom-domains">Custom Domains</x-link> - Allow your customers to use their own domain names with their schedules, including DigitalOcean App Platform setup</li>
            <li><x-link href="/docs/saas/twilio">Twilio Integration</x-link> - Set up phone number verification and WhatsApp messaging</li>
            <li><x-link href="/docs/saas/federation">Federation</x-link> - Share your customers' public events with the eventschedule.com listings, with every listing linking back to your platform</li>
            <li><x-link href="/docs/saas/monetization">Monetization</x-link> - Show ads on your free tier's public pages and sell promotional placement to your paid schedules</li>
            <li><x-link href="/docs/selfhost/stripe">Stripe Integration</x-link> - Keys, webhooks and Stripe Connect for both subscription billing and ticket payments</li>
        </ul>
    </section>

    <!-- Security Considerations -->
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security Considerations
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li><span class="font-semibold text-gray-900 dark:text-white">Environment File:</span> Never expose <code class="doc-inline-code">.env</code> file publicly, and keep <code class="doc-inline-code">APP_DEBUG=false</code> so stack traces never reach a customer</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">HTTPS Required:</span> Always use HTTPS in production, and keep <code class="doc-inline-code">SESSION_SECURE_COOKIE=true</code> so the shared subdomain cookie is never sent in the clear</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">API Keys:</span> Keep all API keys and secrets secure</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Database:</span> Use strong database passwords and restrict access</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">File Permissions:</span> Ensure proper file permissions on the server</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Admin Accounts:</span> The admin panel at <code class="doc-inline-code">/admin</code> reaches every tenant's data. Flag as few accounts as possible as platform admins, and protect them with two-factor authentication</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Proxy Trust:</span> Only widen <code class="doc-inline-code">TRUSTED_PROXIES</code> to <code class="doc-inline-code">*</code> when the origin server cannot be reached except through your proxy. Otherwise list the proxy IPs, so a visitor cannot spoof their own address</li>
        </ol>
    </section>
</x-docs-page>
