<x-docs-page
    key="saas/setup"
    title="White-Label SaaS Setup: Your Own Platform - Event Schedule"
    description="Run Event Schedule as a white-label SaaS: wildcard subdomains, your branding, Stripe plan billing, and tenants selling through their own Stripe or PayPal."
    lede="Configure Event Schedule for SaaS (Software as a Service) deployment, where you host the platform for multiple customers using subdomains."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-group label="Environment Configuration" href="#environment">
            <x-doc-nav-link href="#core-settings">Core SaaS Settings</x-doc-nav-link>
            <x-doc-nav-link href="#branding">Branding</x-doc-nav-link>
            <x-doc-nav-link href="#legal-pages">Legal Pages</x-doc-nav-link>
            <x-doc-nav-link href="#support-email">Support Address</x-doc-nav-link>
            <x-doc-nav-link href="#trials">Trials</x-doc-nav-link>
            <x-doc-nav-link href="#push-notifications">Push Notifications</x-doc-nav-link>
            <x-doc-nav-link href="#google-wallet">Google Wallet Passes</x-doc-nav-link>
            <x-doc-nav-link href="#reverse-proxy">Reverse Proxy</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#dns">DNS Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#webserver">Web Server Configuration</x-doc-nav-link>
        <x-doc-nav-group label="Stripe Subscription Setup" href="#stripe">
            <x-doc-nav-link href="#stripe-variables">Environment Variables</x-doc-nav-link>
            <x-doc-nav-link href="#stripe-webhook">Webhook Endpoint</x-doc-nav-link>
            <x-doc-nav-link href="#stripe-flow">How Subscriptions Work</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#example">Complete Example</x-doc-nav-link>
        <x-doc-nav-link href="#verification">Verification Steps</x-doc-nav-link>
        <x-doc-nav-link href="#demo">Demo Mode</x-doc-nav-link>
        <x-doc-nav-group label="Scheduler and queue" href="#scheduler">
            <x-doc-nav-link href="#http-cron">HTTP cron endpoint</x-doc-nav-link>
            <x-doc-nav-link href="#queue">Queue</x-doc-nav-link>
            <x-doc-nav-link href="#scheduler-health">Knowing that it stopped</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#backup-storage">Backup storage</x-doc-nav-link>
        <x-doc-nav-link href="#support-chat">Support Chat</x-doc-nav-link>
        <x-doc-nav-link href="#translations">Custom translations</x-doc-nav-link>
        <x-doc-nav-link href="#custom-links">Custom dashboard links</x-doc-nav-link>
        <x-doc-nav-link href="#security">Security Considerations</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#related">Related Documentation</x-doc-nav-link>
    </x-slot:toc>

    {{-- Overview --}}
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">This page turns a working selfhosted install into a platform other people sign up to. Each customer schedule gets a subdomain of your domain, your logo and domain are on the product, and paid plans are billed through your own Stripe account. The setup itself is done in <code class="doc-inline-code">.env</code>, your DNS and your web server. Once it is running you operate the platform from the admin panel at <code class="doc-inline-code">/admin</code>, the <strong>Admin</strong> entry in the sidebar of an administrator's account, which the <a href="{{ route('marketing.docs.selfhost.admin') }}" class="doc-link">Admin Panel guide</a> describes tab by tab.</p>

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
                        <td><strong>Selfhosted</strong></td>
                        <td>Path-based <code class="doc-inline-code">/schedule-name/...</code></td>
                        <td>Single organization or personal use</td>
                    </tr>
                    <tr>
                        <td><strong>SaaS/Hosted</strong></td>
                        <td>Subdomain-based <code class="doc-inline-code">schedule-name.yourdomain.com</code></td>
                        <td>Multi-tenant platform for multiple customers</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">In SaaS mode each customer schedule gets its own subdomain, and signing in, the admin portal and billing all live on one shared <code class="doc-inline-code">app</code> subdomain. A schedule on an Enterprise plan can additionally be served from the customer's own domain; see <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="doc-link">Custom Domains</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The <code class="doc-inline-code">app</code> and <code class="doc-inline-code">www</code> hosts never resolve to a schedule, and a list of names the app needs for itself (among them <code class="doc-inline-code">admin</code>, <code class="doc-inline-code">api</code>, <code class="doc-inline-code">blog</code>, <code class="doc-inline-code">docs</code>, <code class="doc-inline-code">demo</code> and anything starting with <code class="doc-inline-code">demo-</code>) cannot be taken as a subdomain. A customer who asks for a reserved name is given a different subdomain instead, and renaming a schedule to one from the admin panel is refused with "That subdomain is reserved".</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your platform does not serve the Event Schedule marketing site</div>
            <p>The marketing pages (home, features, pricing, this user guide) are registered only when
            <code class="doc-inline-code">IS_NEXUS=true</code>, which identifies the one upstream install that
            receives federated events and shared translations. Leave it unset on your own platform. Your root
            domain then redirects visitors to the sign-in page, and you point
            <code class="doc-inline-code">APP_MARKETING_URL</code> at whatever marketing site you run yourself.</p>
        </div>
    </section>

    {{-- Prerequisites --}}
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li>A completed base installation of Event Schedule, including MySQL and the <code class="doc-inline-code">schedule:run</code> cron entry (see <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a>)</li>
            <li>A domain name with DNS access</li>
            <li>Ability to configure wildcard SSL certificates</li>
            <li>Web server configured to handle wildcard subdomains (Apache or Nginx)</li>
            <li>A working mail transport: tenant invitations, ticket confirmations, subscription receipts and support notifications all send from this install. In hosted mode, sale notifications, appointment and gift card emails and buyer change notices go only through a tenant schedule's own email settings, so a tenant without them gets none of those</li>
        </ol>
    </section>

    {{-- Environment Configuration --}}
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Environment Configuration
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Add the following variables to your <code class="doc-inline-code">.env</code> file to enable SaaS mode:</p>

        <h3 id="core-settings" class="doc-subheading">Core SaaS Settings</h3>
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

<span class="code-comment"># Your own marketing site: the base of every link to a page the app does not serve</span>
<span class="code-variable">APP_MARKETING_URL</span>=<span class="code-string">https://yourdomain.com</span></code></pre>
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
                        <td><code class="doc-inline-code">IS_HOSTED</code></td>
                        <td><code class="doc-inline-code">false</code></td>
                        <td>Enable subdomain-based routing for multi-tenant SaaS</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_NAME</code></td>
                        <td><code class="doc-inline-code">Laravel</code></td>
                        <td>Sets the sender name on outgoing email, through the <code class="doc-inline-code">MAIL_FROM_NAME="${APP_NAME}"</code> reference in <code class="doc-inline-code">.env.example</code>. It also names the session cookie (<code class="doc-inline-code">laravel_session</code> for the shipped value) unless you set <code class="doc-inline-code">SESSION_COOKIE</code>, so changing it on a live platform signs everyone out once. It does <strong>not</strong> rename the product: admin page titles are literal, and <code class="doc-inline-code">config('app.name')</code> is a fixed <code class="doc-inline-code">Event Schedule</code> string in <code class="doc-inline-code">config/app.php</code>, which is also the wordmark on mail the platform sends about an account or to a schedule's owner. Public schedule pages are already unbranded, since their title carries the schedule's own name. Rename in-app wording with <a href="#translations" class="doc-link">custom translations</a> instead.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_URL</code></td>
                        <td>-</td>
                        <td>Application URL. Set to the <code class="doc-inline-code">app</code> subdomain (e.g. <code class="doc-inline-code">https://app.yourdomain.com</code>). The base domain is derived by stripping a leading <code class="doc-inline-code">app.</code>, <code class="doc-inline-code">www.</code>, <code class="doc-inline-code">blog.</code> or <code class="doc-inline-code">demo.</code>, and the <code class="doc-inline-code">app</code> and <code class="doc-inline-code">demo</code> hosts and every schedule's subdomain are then built back from it automatically. The <code class="doc-inline-code">blog</code> host belongs to the Event Schedule marketing site and is not served on your platform.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_MARKETING_URL</code></td>
                        <td><code class="doc-inline-code">https://eventschedule.com</code></td>
                        <td>Your own marketing site, and the base of every link the app builds to a page it does not serve itself: the footer strip on your free tier's public pages, the logo in the admin portal's sidebar and on the sign-in pages, the <strong>Help</strong> button (<code class="doc-inline-code">/docs/...</code>), the <strong>Learn more</strong> and <strong>Compare plans</strong> links on upgrade prompts (<code class="doc-inline-code">/features/...</code>, <code class="doc-inline-code">/pricing</code>), and the privacy policy and terms (<code class="doc-inline-code">/privacy</code>, <code class="doc-inline-code">/terms-of-service</code>) until you <a href="#legal-pages" class="doc-link">publish your own</a>. Point it at your site rather than leaving the default, and have your site answer or redirect those paths.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">IS_NEXUS</code></td>
                        <td><code class="doc-inline-code">false</code></td>
                        <td>Leave this off. It marks the single upstream install that hosts the Event Schedule marketing site and receives federated events and shared translation suggestions. Turning it on also changes the default proxy trust and disables the in-app updater.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="branding" class="doc-subheading">Branding Customization</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Logo for light backgrounds (dark artwork)</span>
<span class="code-variable">APP_LOGO_DARK</span>=<span class="code-string">/images/dark_logo.png</span>

<span class="code-comment"># Logo for dark backgrounds (light artwork)</span>
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
                        <td>Logo displayed on light backgrounds: the sign-in and sign-up pages, the first-run page that asks for a schedule type, the <strong>About</strong> dialog and the legal pages you write in the app, all in the light theme</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APP_LOGO_LIGHT</code></td>
                        <td><code class="doc-inline-code">/images/light_logo.png</code></td>
                        <td>Logo displayed on dark backgrounds: the same places in the dark theme. It is also the icon in the platform's own web app manifest.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">For the logo files:</p>
        <ul class="doc-list">
            <li>Place logo files in <code class="doc-inline-code">public/images/</code></li>
            <li>Recommended dimensions: 200px width, transparent background</li>
            <li>Supported formats: PNG, SVG</li>
            <li>The dark logo should have dark/black text (for light backgrounds)</li>
            <li>The light logo should have light/white text (for dark backgrounds)</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The logo at the top of the admin portal's sidebar does not read these variables. It always loads <code class="doc-inline-code">public/images/light_logo.webp</code>, with <code class="doc-inline-code">light_logo.png</code> for a browser without WebP, so replace those two files to change it.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One credit a page</div>
            <p>Your sender name, logos and domain make the platform yours, and your free tier's footer
            strip points at your <code class="doc-inline-code">APP_MARKETING_URL</code> rather than
            ours. One thing is not yours to repoint: a small "Event Schedule" chip in the corner of
            the public pages of every customer you charge. It is the
            attribution the <a href="https://github.com/eventschedule/eventschedule/blob/main/LICENSE" target="_blank" rel="noopener" class="doc-link">Attribution Assurance License</a>
            asks for in return for the software, so it links to eventschedule.com and
            <code class="doc-inline-code">APP_MARKETING_URL</code> does not change it. A free schedule
            shows your footer strip instead of the chip, so no page carries two credits.</p>
        </div>

        <h3 id="legal-pages" class="doc-subheading">Legal Pages</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The privacy policy, terms of service and cookie policy your customers and their visitors are linked to are yours to publish. Until you do, those links go to <code class="doc-inline-code">/privacy</code> and <code class="doc-inline-code">/terms-of-service</code> on your <code class="doc-inline-code">APP_MARKETING_URL</code>, and with that variable left on its default they open eventschedule.com's documents, which name our company and not yours.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Open <strong>System &rarr; Legal Pages</strong> in the admin panel (<code class="doc-inline-code">/admin/legal</code>). Each of the three documents takes either an <strong>External URL</strong>, for a policy hosted elsewhere, or a <strong>Document</strong> written there in Markdown, which the app then serves on your <code class="doc-inline-code">app</code> subdomain. Saving one replaces the link everywhere it appears, including the sign-up page, the ticket checkout and the cookie banner. While the privacy policy is still the built-in one, the admin dashboard shows <strong>Publish your own privacy policy</strong> under <strong>Needs attention</strong>. The <a href="{{ route('marketing.docs.selfhost.admin') }}#system-legal-pages" class="doc-link">Admin Panel guide</a> covers the page.</p>

        <h3 id="support-email" class="doc-subheading">Support Configuration</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># The address your customers are told to write to</span>
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
                        <td>Shown at the foot of every admin portal page ("If you have any questions or suggestions email us at ...") and as <strong>Contact Us</strong> in the <strong>About</strong> dialog. It is also the Reply-To on the notices sent when an account, schedule or event is deleted, and on the email a customer gets when you answer them in the <a href="#support-chat" class="doc-link">support chat</a>. Change it or your customers will write to us.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="trials" class="doc-subheading">Pricing and Trial Configuration</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Length in days of both free trials</span>
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
                        <td>Length, in days, of the two free trials below. The shipped <code class="doc-inline-code">.env.example</code> sets <code class="doc-inline-code">365</code>, so set it deliberately.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A new schedule starts on the <strong>Free</strong> plan. Nothing grants it Pro automatically, so the free tier is what every customer sees first. Two different trials read <code class="doc-inline-code">TRIAL_DAYS</code>:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Trial</th>
                        <th>Where it starts</th>
                        <th>What it opens</th>
                        <th>Who gets it</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Subscription trial</strong></td>
                        <td>When the owner subscribes, from <strong>Upgrade to Pro</strong> on the <strong>Plan</strong> tab or any upgrade prompt. A card is entered, and Stripe takes the first payment when the trial ends.</td>
                        <td>The whole plan they subscribed to</td>
                        <td>A schedule that has never had a plan or a subscription. A schedule that still has days left on a plan with an expiry date, such as one you granted by hand, gets those remaining days as its trial instead.</td>
                    </tr>
                    <tr>
                        <td><strong>Selling trial</strong></td>
                        <td><strong>Sell tickets free for <em>N</em> days</strong>, on the event form's <strong>Tickets</strong> tab and on the <strong>Plan</strong> tab. No card is asked for, and it is offered whether or not your Stripe keys are set.</td>
                        <td>Selling tickets that carry a price, and nothing else. The schedule stays on Free, and other Pro features stay locked.</td>
                        <td>An owner once, across all their schedules, and never one who has had a subscription. A reminder email goes out three days before it ends and again one day before.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A schedule that qualifies for the subscription trial sees a free-trial notice beside <strong>Upgrade to Pro</strong> and at the top of the subscribe page, with the date of the first charge. The notice's label is fixed at "7-day free trial" whatever <code class="doc-inline-code">TRIAL_DAYS</code> holds, so if you use another length, change that string (<code class="doc-inline-code">free_trial_badge</code>) with <a href="#translations" class="doc-link">custom translations</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">What a subscriber is charged after a trial comes from the Price objects in your Stripe dashboard. The app only stores the Price IDs, plus separate display amounts (see <a href="#stripe" class="doc-link">Stripe Subscription Setup</a>). The customer's side of the selling trial is in <a href="{{ route('marketing.docs.tickets') }}#selling-trial" class="doc-link">Selling Tickets</a>.</p>

        <h3 id="push-notifications" class="doc-subheading">Push Notifications (Optional)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Event Schedule can send web push notifications that mirror its email notifications using <a href="https://onesignal.com" target="_blank" rel="noopener noreferrer" class="doc-link">OneSignal</a>. This is a Pro feature and is <strong>off by default</strong>: with no configuration, no push SDK loads and no calls are made to OneSignal. To enable it platform-wide, create a OneSignal app (Web platform) and set:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">ONESIGNAL_APP_ID</span>=<span class="code-string">your-onesignal-app-id</span>
<span class="code-variable">ONESIGNAL_REST_API_KEY</span>=<span class="code-string">your-onesignal-rest-api-key</span></code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once both values are set, a <strong>Push notifications</strong> panel appears on each schedule's edit page, in the <strong>Notifications</strong> row of the <strong>Settings</strong> tab, where the owner presses <strong>Enable push on this device</strong> and can send a test. Sending is gated on the schedule being Pro or Enterprise, and the demo schedule never receives push. One OneSignal app serves the whole platform; tenants are segmented automatically. Add <code class="doc-inline-code">ONESIGNAL_SAFARI_WEB_ID</code> only if you need legacy macOS Safari support.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Note that enabling push loads the OneSignal SDK from their CDN and sends notification data to OneSignal, and that Apple iOS only supports web push for sites added to the home screen (iOS 16.4+).</p>

        <h3 id="google-wallet" class="doc-subheading">Google Wallet Passes (Optional)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Ticket buyers can keep their ticket in Google Wallet: an <strong>Add to Google Wallet</strong> button appears on the ticket page, the multi-event order page and the confirmation email, and the pass carries the same QR code, so it scans at the door like any other ticket. One Google Wallet issuer account of yours switches it on for every tenant at once, on every plan, and tenants have nothing to connect. Set:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">GOOGLE_WALLET_ISSUER_ID</span>=<span class="code-string">your-issuer-id</span>
<span class="code-variable">GOOGLE_WALLET_SERVICE_ACCOUNT</span>=<span class="code-string">/absolute/path/to/service-account.json</span></code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The service account setting also takes the key file's contents, base64-encoded, for a host with no writable file mount. Leave either value empty and no button renders and nothing is sent to Google. A new issuer account starts in Google's demo mode, where only the Google accounts you register as testers can save a pass. Give a staging install its own <code class="doc-inline-code">GOOGLE_WALLET_ID_PREFIX</code> (the default is <code class="doc-inline-code">es</code>): Google never deletes a pass class, so two installs sharing an issuer account and a prefix collide for good. The <a href="{{ route('marketing.docs.selfhost.google_wallet') }}" class="doc-link">Google Wallet guide</a> covers creating the issuer account.</p>

        <h3 id="reverse-proxy" class="doc-subheading">Running Behind a Reverse Proxy</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A multi-tenant install almost always sits behind a reverse proxy or CDN (Nginx, Apache, Cloudflare, or a control panel such as HestiaCP). Tell Event Schedule which proxies to trust so it reads the <code class="doc-inline-code">X-Forwarded-Proto</code> and <code class="doc-inline-code">X-Forwarded-For</code> headers those proxies set:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">TRUSTED_PROXIES</span>=<span class="code-value">*</span></code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use <code class="doc-inline-code">*</code> to trust any proxy, or a comma-separated list of proxy IPs or CIDR ranges (for example <code class="doc-inline-code">10.0.0.0/8,192.168.1.1</code>) when the origin server is reachable directly from the internet. Left unset, your platform trusts no proxies at all: the application then treats every request as plain HTTP even when the browser is on HTTPS, which can produce redirect loops on tenant subdomains, and it records the proxy's IP address as the visitor's IP in analytics and rate limiting.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The setting deliberately lives in <code class="doc-inline-code">config/trustedproxy.php</code> rather than in application bootstrap, so it survives <code class="doc-inline-code">php artisan config:cache</code>. Re-run that command after changing the value.</p>
    </section>

    {{-- DNS Configuration --}}
    <section id="dns" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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

    {{-- Web Server Configuration --}}
    <section id="webserver" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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

    {{-- Stripe Subscription Setup --}}
    <section id="stripe" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Stripe Subscription Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">To sell paid plans to your customers, configure Stripe subscription billing. The subscription charges are made on your own Stripe account. This is separate from ticket payments, which settle into each schedule owner's own account with no platform fee: cards through Stripe Connect, or the owner's own <a href="{{ route('marketing.docs.tickets') }}#paypal" class="doc-link">PayPal</a>, Payfast or Invoice Ninja account, a payment link, or cash. In SaaS mode there is no install-wide PayPal or Payfast account for your platform to supply, so each tenant connects their own, and a refund from the Sales page goes back through the account that took the money.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">See the <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Stripe integration documentation</a> for step-by-step key, webhook and Connect instructions.</p>

        <h3 id="stripe-variables" class="doc-subheading">Required Environment Variables</h3>
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">Those five cover the Pro tier. Selling Enterprise, and showing the right numbers in the interface, needs these as well:</p>

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
                        <td>Stripe Price ID for monthly Enterprise. The <strong>Upgrade to Enterprise</strong> button is hidden until both Enterprise Price IDs are set.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY</code></td>
                        <td>-</td>
                        <td>Stripe Price ID for yearly Enterprise</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_PRICE_MONTHLY_AMOUNT</code><br><code class="doc-inline-code">STRIPE_PRICE_YEARLY_AMOUNT</code></td>
                        <td><code class="doc-inline-code">5</code> / <code class="doc-inline-code">50</code></td>
                        <td>Display-only Pro amounts shown on the subscribe page, the Plan tab and upgrade prompts. An administrator can change them in the <strong>Plan pricing</strong> card under <strong>System &rarr; Settings</strong> in the admin panel (<code class="doc-inline-code">/admin/settings</code>), which overrides these for everything the site displays</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT</code><br><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT</code></td>
                        <td><code class="doc-inline-code">15</code> / <code class="doc-inline-code">150</code></td>
                        <td>Display-only Enterprise amounts, overridable in the same <strong>Plan pricing</strong> card</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">PLATFORM_CURRENCY</code></td>
                        <td><code class="doc-inline-code">USD</code></td>
                        <td>The currency those amounts are shown in, everywhere the platform quotes its own price. Also the fallback currency for a new event whose schedule has no country. An administrator can change it in the <strong>Platform currency</strong> card on the same page, which overrides this value</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The <code class="doc-inline-code">*_AMOUNT</code> variables and <code class="doc-inline-code">PLATFORM_CURRENCY</code> are labels, not prices. What a customer is charged comes from the Stripe Price the matching Price ID points at, including its currency, and nothing reconciles the two. Set them all, and keep them in step, or your platform will advertise one figure and bill another.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Keep the <code class="doc-inline-code">*_AMOUNT</code> variables set even once you are editing the numbers in the admin panel. Revenue reporting and renewal emails read the variables, not the <strong>Plan pricing</strong> card, so that an amount changed to run a promotion cannot restate revenue you have already booked or quote an existing subscriber a figure their card will never be charged. One thing does turn the advertised amount into money: a <a href="{{ route('marketing.docs.referral_program') }}" class="doc-link">referral</a> credit applied to a subscribed schedule is posted to its Stripe balance at the advertised monthly amount.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Do not repoint a Price ID that still has subscribers on it</div>
            <p>A subscription is matched to its plan by comparing its Stripe Price ID with the four you have configured, and with nothing else. Stripe Prices cannot be edited, so changing what a plan costs means a new Price object. A subscriber left on a Price ID that is no longer in <code class="doc-inline-code">.env</code> stops resolving: an Enterprise subscriber loses the Enterprise features while their card is still charged for them, and revenue reporting counts them at zero. The admin dashboard then shows <strong>subscriptions on an unrecognized price</strong> under <strong>Needs attention</strong>, linking to the list on <strong>Insights &rarr; Revenue</strong>. Move those subscriptions onto the new Price in Stripe, or keep the variable on the Price they are on.</p>
        </div>

        <h3 id="stripe-webhook" class="doc-subheading">Webhook Endpoint</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Subscriptions are kept in sync by a webhook that is separate from the ticket-payment one. In your Stripe dashboard add an endpoint pointing at <code class="doc-inline-code">https://app.yourdomain.com/stripe/subscription-webhook</code> and copy its signing secret into <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>. It is this webhook that downgrades a schedule to Free when its subscription is deleted, and that raises the payment-failed notice, so without it a cancellation in Stripe never reaches your platform.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Do not leave the signing secret blank</div>
            <p>Signature checking is only switched on when <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>
            has a value. Leave it empty and the endpoint stays open, accepting unsigned requests that could downgrade or
            upgrade any schedule on your platform. Set it as soon as you create the endpoint.</p>
        </div>

        <h3 id="stripe-flow" class="doc-subheading">How Subscriptions Work</h3>
        <ol class="doc-list doc-list-numbered">
            <li>A customer creates a schedule. It starts on the Free plan</li>
            <li>They open the schedule and select its <strong>Plan</strong> tab, the last of the schedule's tabs, which shows the current plan, its status, and how much of the newsletter email and photo allowances is used</li>
            <li>The schedule's owner clicks <strong>Upgrade to Pro</strong> and pays. The button only appears once <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> is set, and only for the owner: other team members see the plan but none of its actions</li>
            <li>Pro features unlock for that schedule, and the free-tier footer strip and ad slot come off its public pages</li>
            <li>An active Pro subscriber can then use <strong>Upgrade to Enterprise</strong>, or <strong>Switch to Yearly</strong> and <strong>Switch to Monthly</strong>, from the same tab. <strong>Manage Subscription</strong> opens the Stripe billing portal, and <strong>Cancel Subscription</strong> asks for an optional reason and keeps the plan until the end of the paid period</li>
            <li>Subscriptions are per schedule, not per user: a customer with three schedules pays for each one they upgrade</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To put a schedule on a plan without a subscription, open <strong>Manage &rarr; Schedules</strong> in the admin panel, press <strong>Edit</strong> on its row, and under <strong>Plan</strong> set <strong>Plan Type</strong> and a <strong>Plan Expires</strong> date. The plan holds until that date, so a plan type saved with no date grants nothing. See <a href="{{ route('marketing.docs.selfhost.admin') }}#manage-plans" class="doc-link">Schedules</a> in the Admin Panel guide.</p>
    </section>

    {{-- Complete Example Configuration --}}
    <section id="example" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">The session cookie spans your subdomains</div>
            <p>Set <code class="doc-inline-code">SESSION_DOMAIN</code> to <code class="doc-inline-code">.yourdomain.com</code> (with leading dot) to allow session sharing across subdomains. If left unset, hosted mode automatically defaults it to your <code class="doc-inline-code">APP_URL</code> base domain; setting it explicitly takes precedence.</p>
            <p class="mt-2">Requests arriving on a customer's own domain are the exception: the session domain is cleared for those requests only, so the cookie is scoped to that origin instead of one the browser would reject. That is also why signing in always happens on your <code class="doc-inline-code">app</code> subdomain rather than on a custom domain.</p>
        </div>
    </section>

    {{-- Verification Steps --}}
    <section id="verification" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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
            <li>Open the same address in a private window. Signed out, a schedule answers page-not-found until its email address or phone number has been verified, so verify the schedule's email first and then check that the page loads for a visitor</li>
        </ol>

        <h3 class="doc-subheading">3. Test SSL Certificate</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-2">Verify SSL works for both:</p>
        <ul class="doc-list mb-6">
            <li>Main domain: <code class="doc-inline-code">https://yourdomain.com</code></li>
            <li>Any subdomain: <code class="doc-inline-code">https://test.yourdomain.com</code></li>
        </ul>

        <h3 class="doc-subheading">4. Test Subscription Flow (if configured)</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Signed in as the schedule's owner, open the schedule and select its <strong>Plan</strong> tab</li>
            <li>Click <strong>Upgrade to Pro</strong>. If the button is missing, <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> is not set</li>
            <li>Complete checkout with the test card <code class="doc-inline-code">4242 4242 4242 4242</code>, which only works while your keys are the <code class="doc-inline-code">sk_test_</code> / <code class="doc-inline-code">pk_test_</code> pair</li>
            <li>Confirm the Plan tab now reports Pro, and that the free-tier footer strip has gone from the schedule's public page</li>
            <li>Cancel from the Stripe dashboard and confirm the Plan tab picks it up, which proves the subscription webhook is wired correctly</li>
        </ol>
    </section>

    {{-- Demo Mode --}}
    <section id="demo" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
            </svg>
            Demo Mode
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Demo mode lets potential customers try your platform without signing up. Visitors to <code class="doc-inline-code">demo.yourdomain.com</code> are automatically logged in to a shared demo account with sample data.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">It is part of every hosted install and has no switch: with <code class="doc-inline-code">IS_HOSTED=true</code> the <a href="#scheduler" class="doc-link">scheduler</a> creates the demo account and its schedules the first time it runs, and rebuilds them every hour after that. On a selfhosted install the command refuses to run and the auto-login stays inert, since demo mode relies on subdomain routing.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ul class="doc-list mb-6">
            <li>A request to the <code class="doc-inline-code">demo</code> subdomain signs the visitor in as the demo user, with no password prompt</li>
            <li>They land in the <strong>admin portal</strong> for the demo schedule, on its <strong>Schedule</strong> tab, so what they try is the real product rather than a public page</li>
            <li>The demo interface follows the visitor's browser language, chosen from your supported languages on first visit</li>
            <li>A visitor already signed in as a real user is bounced back to your app rather than switched into the demo</li>
            <li>The sample data is rebuilt every hour, so what one visitor changed is gone for the next</li>
        </ul>

        <h3 id="demo-data" class="doc-subheading">What Is Created, and When</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The scheduler runs the setup command once an hour, whichever way you drive it. The first run creates the demo, and every later run resets it. Run it by hand to have the demo at once, or to reset it between two scheduled runs:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan app:setup-demo</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">It creates the demo user and a curator schedule on the <code class="doc-inline-code">simpsons</code> subdomain, then populates a small Springfield-themed network around it on <code class="doc-inline-code">demo-</code> subdomains: talent and venue schedules, sub-schedules, events with ticket types, followed schedules, sample ticket purchases and analytics history. A reset deletes and recreates the <code class="doc-inline-code">demo-</code> schedules and empties the curator. An event one of your customers created that reached a demo schedule is only detached from it, and keeps its tickets and sales.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Do not add a cron entry of your own for the command. The scheduler already runs it, and a second runner would rebuild the demo twice an hour.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Let the scheduler run once before you open sign-ups</div>
            <p>The demo account always has the address <code class="doc-inline-code">contact@eventschedule.com</code>, and its curator schedule is always <code class="doc-inline-code">simpsons</code>. If a real account already holds that address, or a real schedule that subdomain, the scheduler adopts it as the demo: every visitor to the demo host is signed in to that account, and each hourly reset deletes that schedule's events and sub-schedules. Once the demo exists both names are taken.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The demo schedule is created on the Free plan like any other, so Pro-only screens stay locked and its public pages carry your free-tier footer. To show off paid features, open <strong>Manage &rarr; Schedules</strong> in the admin panel, press <strong>Edit</strong> on the <code class="doc-inline-code">simpsons</code> row, and under <strong>Plan</strong> set <strong>Plan Type</strong> and a <strong>Plan Expires</strong> date. Whatever its plan, it never shows ads or the accommodation map. Its description ends with a line that sends visitors to eventschedule.com.</p>
    </section>

    {{-- Scheduler and queue --}}
    <section id="scheduler" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Scheduler and queue
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Timed work (reminder emails, calendar sync, installment charges, ticket release, AI translation, the hourly demo reset) is driven by the Laravel scheduler, and the queue is drained from inside it. There are three ways to run it: a cron entry, a worker process, or an HTTP endpoint. Pick one.</p>

        <h3 id="scheduler-cron" class="doc-subheading">A cron entry, or a worker process</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">On a server with cron, add the single entry from the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">installation guide</a>:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>* * * * * php /path/to/eventschedule/artisan schedule:run</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">On a platform with no cron but long-running processes, a container host for example, run the scheduler as the process instead. This is what eventschedule.com does, as a DigitalOcean App Platform worker:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan schedule:work</code></pre>
        </div>

        <h3 id="http-cron" class="doc-subheading">Or an HTTP cron endpoint</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Shared hosting that offers neither a crontab nor a long-running process can drive the same schedule over HTTP. Set <code class="doc-inline-code">APP_CRON_SECRET</code> to a long random string and have any external cron service request this once a minute:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>http</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>GET https://app.yourdomain.com/translate_data?secret=YOUR_SECRET</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The same secret also gates <code class="doc-inline-code">/release_tickets</code>. Leaving <code class="doc-inline-code">APP_CRON_SECRET</code> empty disables both endpoints, which is the right setting on an install that uses cron or a worker.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Do not run two of these at once</div>
            <p>Each of the three is a complete copy of the same schedule. An install running two of them, or the same one twice, will do some work twice. A handful of commands hold a shared lock and are safe either way, but most rely on there being a single runner.</p>
        </div>

        <h3 id="queue" class="doc-subheading">Queue</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The scheduler runs <code class="doc-inline-code">queue:work --stop-when-empty</code> every minute, so queued mail and background jobs go out within about a minute with no separate worker to manage. With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> nothing is queued at all and jobs run inline in the request that created them; set it to <code class="doc-inline-code">database</code> to get the queue.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you need lower latency than a minute, run a resident <code class="doc-inline-code">php artisan queue:work</code> as well.</p>

        <h3 id="scheduler-health" class="doc-subheading">Knowing that it stopped</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every scheduler tick stamps a heartbeat. When it goes stale the admin panel shows <strong>Scheduled tasks are not running</strong> under <strong>Needs attention</strong> on the dashboard, puts a badge on the <strong>System</strong> tab, and says so at the top of <strong>System &rarr; Queue</strong>. Tune the threshold with <code class="doc-inline-code">SCHEDULER_STALE_MINUTES</code> (default 20, and never less than 16). Take it seriously: while the scheduler is down nothing sends email, syncs a calendar or charges an installment.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong>Scheduler</strong> card on <strong>System &rarr; Queue</strong> is where to look first. It shows when the last tick was, each rail that has ticked and how long ago, how many scheduled tasks are reporting, any task that failed, and which cache store holds the heartbeat and whether every server can read it. An install driven only by the HTTP endpoint reports the heartbeat alone, since that rail records no per-task results.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If the scheduler is a container of its own beside the web server, two more variables make a dead one visible:</p>

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
                        <td><code class="doc-inline-code">SCHEDULER_RAIL</code></td>
                        <td><code class="doc-inline-code">cron</code></td>
                        <td>The name this process ticks under on the Scheduler card. Set it to <code class="doc-inline-code">worker</code> on the scheduler container, because <code class="doc-inline-code">schedule:run</code> cannot tell a crontab from a worker. The HTTP endpoint always ticks as <code class="doc-inline-code">http</code>.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">SCHEDULER_EXPECTED_RAIL</code></td>
                        <td>-</td>
                        <td>The rail that must be alive for scheduled work to count as happening. Leave it unset with a single cron. Set it, on the web server as well, to the same name as the scheduler container's <code class="doc-inline-code">SCHEDULER_RAIL</code>: without it another rail keeping the shared heartbeat fresh hides a dead worker, and with a name nothing ticks under the panel reports a stall that is not real.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Running more than one app server</div>
            <p>Set <code class="doc-inline-code">CACHE_STORE</code> to <code class="doc-inline-code">database</code> or <code class="doc-inline-code">redis</code>. Every lock that stops two scheduled runs colliding is held in the cache, so on the <code class="doc-inline-code">file</code> default each server serialises only against itself, and the heartbeat above will report a stall that is not real, because one server cannot see another's cache.</p>
        </div>
    </section>

    {{-- Backup storage --}}
    <section id="backup-storage" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            Backup storage
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Schedule exports are written to <code class="doc-inline-code">storage/app</code> by default, which is correct for a single server. Point them at object storage if your app runs on more than one server or container, or on a host with an ephemeral filesystem: the process that builds an export is not the one that later serves the download, so a local file would be missing or already deleted by then.</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">BACKUP_DISK_DRIVER</span>=<span class="code-value">s3</span>
<span class="code-variable">BACKUP_SPACES_KEY</span>=<span class="code-string">your-key</span>
<span class="code-variable">BACKUP_SPACES_SECRET</span>=<span class="code-string">your-secret</span>
<span class="code-variable">BACKUP_SPACES_REGION</span>=<span class="code-string">nyc3</span>
<span class="code-variable">BACKUP_SPACES_ENDPOINT</span>=<span class="code-string">https://nyc3.digitaloceanspaces.com</span>
<span class="code-variable">BACKUP_SPACES_BUCKET</span>=<span class="code-string">your-private-backups-bucket</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The endpoint is your provider's <strong>region</strong> endpoint, not the per-bucket origin endpoint that storage consoles display beside the bucket itself. The bucket name is added to the hostname for you, so an endpoint that already carries it addresses <code class="doc-inline-code">bucket.bucket.region...</code> and every upload fails its TLS handshake. Event Schedule strips a leading bucket name if it finds one, so either form works.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Use a separate private bucket, never your images bucket</div>
            <p>An export archive contains every sale, attendee email address and phone number for the schedules inside it, and its path is a user id plus a timestamp. Image buckets are public and usually CDN-fronted, so anything landing in one is effectively published at a guessable URL, and a CDN keeps serving it after you make the object private again. Use a bucket with no public policy and no CDN in front of it. <code class="doc-inline-code">BACKUP_SPACES_BUCKET</code> has no default for this reason: a missing value fails rather than quietly writing backups somewhere public.</p>
            <p>Archive filenames also carry 32 random characters, so the bucket is not the only thing standing between an export and the public.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Imports are unaffected and always stay on the server that received the upload.</p>
    </section>

    {{-- Support Chat --}}
    <section id="support-chat" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" class="inline-block w-7 h-7 me-2 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Support Chat
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule includes a built-in chat system that lets your customers message you for support without leaving the admin portal. It needs no configuration and is present on every hosted install; on a selfhosted install neither the widget nor the admin screen exists. Each customer has one running conversation with you, which reopens if they write again after you have closed it.</p>

        <h3 id="support-chat-customers" class="doc-subheading">For Your Customers</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4>Chat widget</h4>
                <p>A floating chat bubble in the bottom corner of the screen for signed-in users, with a green dot on it while you are marked available.</p>
            </div>
            <div class="doc-field">
                <h4>Sidebar button</h4>
                <p>A chat icon beside <strong>Help</strong> at the foot of the admin sidebar opens the same panel, with a red badge for unread replies.</p>
            </div>
            <div class="doc-field">
                <h4>Message limit</h4>
                <p>Up to 2,000 characters per message, in both directions. A message is stored as typed and escaped wherever it is shown, so HTML in one appears as text.</p>
            </div>
        </div>

        <h3 id="support-chat-admin" class="doc-subheading">For You, the Platform Admin</h3>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Inbox</h4>
                <p>Manage conversations from <strong>System &rarr; Support</strong> in the admin panel (<code class="doc-inline-code">/admin/support</code>). Every conversation is listed with its unread count, and the <strong>System</strong> tab and its <strong>Support</strong> entry carry a matching badge.</p>
            </div>
            <div class="doc-field">
                <h4>Availability switch</h4>
                <p>Switch yourself online to show the green dot, either at the top of the Support page or from the chat icon at the foot of the sidebar, which for an admin opens an availability switch and an <strong>Open inbox</strong> link instead of a chat.</p>
            </div>
            <div class="doc-field">
                <h4>Hourly check</h4>
                <p>While you are online, every hour the admin portal asks whether you are still available. Confirm within 10 minutes or you are switched offline, so you never leave it on overnight by accident. Replying to a conversation counts as confirming.</p>
            </div>
            <div class="doc-field">
                <h4>Away when the admin portal is closed</h4>
                <p>If no admin portal tab of yours has checked in for 5 minutes (the laptop is closed or the browser quit), customers see you as away until you open it again. Only the admin who switched the chat on counts, so another admin's open tab does not keep you looking available.</p>
            </div>
            <div class="doc-field">
                <h4>New message alerts</h4>
                <p>While you are online, a new message shows a notice with a <strong>Reply</strong> link, plays a soft chime and flashes the tab title on whichever admin portal page you are on.</p>
            </div>
            <div class="doc-field">
                <h4>Replying</h4>
                <p>Open a conversation to read the history and reply. The customer sees a typing indicator while you write, and you see "Seen" once they have read your reply.</p>
            </div>
            <div class="doc-field">
                <h4>Closing conversations</h4>
                <p>Close resolved conversations to keep the list short.</p>
            </div>
        </div>

        <h3 id="support-chat-notifications" class="doc-subheading">Who Gets Notified</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4>When a customer writes: email</h4>
                <p>Every new message emails the first account flagged as a platform admin (the one with the lowest ID), whether you are online or not, so every conversation reaches one inbox. Give that account a monitored address. A burst of messages is one email: the next message is emailed once 10 minutes have passed, or once you have replied to or closed the conversation. While you are available the in-app alert tells you as well.</p>
            </div>
            <div class="doc-field">
                <h4>When a customer writes: push</h4>
                <p>New messages also send a push notification if OneSignal is configured (for a website visitor, at most one every two minutes). It goes to the admin who switched the chat on, or to the same first admin while nobody is online.</p>
            </div>
            <div class="doc-field">
                <h4>When you reply</h4>
                <p>Your replies email the customer only once they have left the chat without reading them. The email waits at least two minutes and carries every unread reply at once, so a quick back-and-forth does not fill their inbox. Its Reply-To is your <code class="doc-inline-code">SUPPORT_EMAIL</code>.</p>
            </div>
        </div>

        <h3 id="support-chat-visitors" class="doc-subheading">Website Visitors</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">On eventschedule.com, the marketing site also offers signed-out visitors a chat with a person, and only while you are available. Visitors can leave an email so a reply reaches them after they leave, and it is required once you are away. Their conversations appear in the same Support inbox, marked Visitor, with the page they are on and their country. This needs the marketing site, so it does not apply to your own SaaS install.</p>
    </section>

    {{-- Custom translations --}}
    <section id="translations" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Custom translations
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Rename built-in UI terms to match your customers' vocabulary (for example "Talent" to "Artist", or "Curator" to "Event Planner") without your changes being wiped out by <code class="doc-inline-code">php artisan app:update</code>. Overrides apply globally across every tenant on your platform.</p>

        <h3 id="translations-manager" class="doc-subheading">The Easy Way: The Translation Manager</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Sign in as a platform admin and open <strong>System &rarr; Translations</strong> in the admin panel (<code class="doc-inline-code">/admin/translations</code>). Search for a phrase, edit it for the locale you want, and save. The database is the source of truth: each save is stored as an override and republished to a file on disk, so nothing is lost on the next upgrade. Reverting an override restores the bundled string. The <a href="{{ route('marketing.docs.selfhost.admin') }}#system-translations" class="doc-link">Admin Panel guide</a> covers the page, including sharing your improvements with the community.</p>

        <h3 id="translations-files" class="doc-subheading">The Manual Way: Override Files</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">You can also drop a PHP file in:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>path</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>storage/app/lang/{locale}/{file}.php</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The three files the Translation Manager works on are <code class="doc-inline-code">messages.php</code> (UI strings), <code class="doc-inline-code">accessibility.php</code>, and <code class="doc-inline-code">marketing.php</code>. List the keys you want to change and nothing else; the bundled translations fill in the rest:</p>
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">A hand-written file for one of those three managed groups is adopted into the database the next time the overrides are republished, after which the file is regenerated from the database. Keep that in mind if you edit both by hand and through the admin panel, and keep nested array values in their own group file (<code class="doc-inline-code">validation.php</code>, <code class="doc-inline-code">auth.php</code> or a custom group), which the loader honours and never rewrites.</p>

        <h3 id="translations-publish" class="doc-subheading">Rebuilding and Moving Servers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The files are server-local derived state, so rebuild them from the database after restoring a backup or cloning the app to a new machine:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan translations:publish</code></pre>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Run it on each web server, and restart your queue workers afterwards so long-running processes pick up the new strings. If you run several servers behind a load balancer, set <code class="doc-inline-code">LANG_OVERRIDES_PATH</code> to a shared volume instead and publish once. A relative value resolves from the application root; an absolute one is used as given.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why this works</div>
            <p>Changes apply on the next request, with no cache clear required. <code class="doc-inline-code">storage/app/</code> is gitignored, so your overrides survive <code class="doc-inline-code">php artisan app:update</code>, <code class="doc-inline-code">git pull</code>, and fresh checkouts.</p>
        </div>
    </section>

    {{-- Custom dashboard links --}}
    <section id="custom-links" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            Custom dashboard links
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add up to three custom links to the admin sidebar (for example a support site, community forum, or status page). On a SaaS deployment these links are <strong>platform-wide</strong>: everyone signed in to the admin portal, on every tenant, sees them just below the <strong>Newsletters</strong> link, and they open in a new tab.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Set the following variables in your <code class="doc-inline-code">.env</code> file. A link only appears when <strong>both</strong> its title and URL are filled in, so you can configure one, two, or three links:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">CUSTOM_LINK_1_TITLE</span>=<span class="code-string">"Help Center"</span>
<span class="code-variable">CUSTOM_LINK_1_URL</span>=<span class="code-string">"https://help.example.com"</span>
<span class="code-variable">CUSTOM_LINK_2_TITLE</span>=<span class="code-string">"Status"</span>
<span class="code-variable">CUSTOM_LINK_2_URL</span>=<span class="code-string">"https://status.example.com"</span>
<span class="code-variable">CUSTOM_LINK_3_TITLE</span>=
<span class="code-variable">CUSTOM_LINK_3_URL</span>=</code></pre>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Reload cached config</div>
            <p>If you have run <code class="doc-inline-code">php artisan config:cache</code>, re-run it (or <code class="doc-inline-code">php artisan config:clear</code>) after editing <code class="doc-inline-code">.env</code> so the new links take effect.</p>
        </div>
    </section>

    {{-- Security Considerations --}}
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security Considerations
        </h2>
        <ul class="doc-list">
            <li><strong>Environment File:</strong> Never expose <code class="doc-inline-code">.env</code> file publicly, and keep <code class="doc-inline-code">APP_DEBUG=false</code> so stack traces never reach a customer</li>
            <li><strong>HTTPS Required:</strong> Always use HTTPS in production, and keep <code class="doc-inline-code">SESSION_SECURE_COOKIE=true</code> so the shared subdomain cookie is never sent in the clear</li>
            <li><strong>API Keys:</strong> Keep all API keys and secrets secure</li>
            <li><strong>Database:</strong> Use strong database passwords and restrict access</li>
            <li><strong>File Permissions:</strong> Ensure proper file permissions on the server</li>
            <li><strong>Admin Accounts:</strong> The admin panel at <code class="doc-inline-code">/admin</code> reaches every tenant's data. Flag as few accounts as possible as platform admins, and protect them with two-factor authentication (<strong>Settings &rarr; Security</strong> in each account). The panel also asks for the password again before it opens, and again after a day without use: see <a href="{{ route('marketing.docs.selfhost.admin') }}#accessing" class="doc-link">Accessing /admin</a></li>
            <li><strong>Proxy Trust:</strong> Only widen <code class="doc-inline-code">TRUSTED_PROXIES</code> to <code class="doc-inline-code">*</code> when the origin server cannot be reached except through your proxy. Otherwise list the proxy IPs, so a visitor cannot spoof their own address</li>
        </ul>
    </section>

    {{-- Troubleshooting --}}
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">Common Issues</h3>

        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Subdomains show 404 or wrong page</h4>
                <ul class="doc-list text-sm">
                    <li>Check that <code class="doc-inline-code">IS_HOSTED=true</code> is set</li>
                    <li>Verify wildcard DNS is configured correctly</li>
                    <li>Ensure web server is configured for wildcard subdomains</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>A schedule's page loads for its owner and is not found for everyone else</h4>
                <ul class="doc-list text-sm">
                    <li>Nobody has verified the schedule's email address or phone number yet. Until one is verified only its own team and administrators can open its public pages</li>
                    <li>Have the owner verify the address, or open <strong>Manage &rarr; Schedules</strong> in the admin panel, press <strong>Edit</strong> on the schedule and use <strong>Mark Email as Verified</strong> under <strong>Verification</strong></li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>Signed in on the app subdomain, signed out on a schedule's subdomain</h4>
                <ul class="doc-list text-sm">
                    <li>Set <code class="doc-inline-code">SESSION_DOMAIN=.yourdomain.com</code> (with leading dot). If unset, hosted mode defaults it to the <code class="doc-inline-code">APP_URL</code> base domain</li>
                    <li>Make sure <code class="doc-inline-code">APP_URL</code> is set to your app subdomain (e.g. <code class="doc-inline-code">https://app.yourdomain.com</code>)</li>
                    <li>Clear browser cookies and try again</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>Redirect loop, or every visitor logged with the same IP address</h4>
                <ul class="doc-list text-sm">
                    <li>Set <code class="doc-inline-code">TRUSTED_PROXIES</code>. Left unset, your platform trusts no proxies and reads HTTPS requests as HTTP (see <a href="#reverse-proxy" class="doc-link">Running Behind a Reverse Proxy</a>)</li>
                    <li>Re-run <code class="doc-inline-code">php artisan config:cache</code> if you have cached your configuration</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>The root domain shows the sign-in page instead of a landing page</h4>
                <ul class="doc-list text-sm">
                    <li>This is expected. Marketing pages are only served when <code class="doc-inline-code">IS_NEXUS=true</code>, which is not a setting for your platform</li>
                    <li>Host your own marketing site and point <code class="doc-inline-code">APP_MARKETING_URL</code> at it</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>SSL certificate errors on subdomains</h4>
                <ul class="doc-list text-sm">
                    <li>Verify wildcard certificate covers <code class="doc-inline-code">*.yourdomain.com</code></li>
                    <li>Check certificate is properly installed in web server</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>Logo not displaying</h4>
                <ul class="doc-list text-sm">
                    <li>Verify logo files exist in <code class="doc-inline-code">public/images/</code></li>
                    <li>Check file permissions are readable</li>
                    <li>Ensure paths in <code class="doc-inline-code">.env</code> match actual file locations</li>
                    <li>The logo in the admin portal's sidebar is not one of the two variables: see <a href="#branding" class="doc-link">Branding Customization</a></li>
                </ul>
            </div>

            <div class="doc-field">
                <h4>The Help button or a Learn more link opens a page that does not exist</h4>
                <ul class="doc-list text-sm">
                    <li>Those links are built on <code class="doc-inline-code">APP_MARKETING_URL</code>, so your marketing site has to answer or redirect <code class="doc-inline-code">/docs/...</code>, <code class="doc-inline-code">/features/...</code> and <code class="doc-inline-code">/pricing</code> (see <a href="#core-settings" class="doc-link">Core SaaS Settings</a>)</li>
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

    {{-- Related Documentation --}}
    <section id="related" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            Related Documentation
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.saas.custom_domains') }}" class="doc-link">Custom Domains</a> - Allow your customers to use their own domain names with their schedules, including DigitalOcean App Platform setup</li>
            <li><a href="{{ route('marketing.docs.saas.twilio') }}" class="doc-link">Twilio Integration</a> - Set up phone number verification and WhatsApp messaging</li>
            <li><a href="{{ route('marketing.docs.saas.facebook_login') }}" class="doc-link">Facebook Login</a> - Let your customers sign up and log in with Facebook</li>
            <li><a href="{{ route('marketing.docs.saas.federation') }}" class="doc-link">Federation</a> - Share your customers' public events with the eventschedule.com listings, with every listing linking back to your platform</li>
            <li><a href="{{ route('marketing.docs.saas.monetization') }}" class="doc-link">Monetization</a> - Show ads on your free tier's public pages, sell promotional placement to your paid schedules, and earn an accommodation affiliate commission</li>
            <li><a href="{{ route('marketing.docs.selfhost.admin') }}" class="doc-link">Admin Panel</a> - Grant plans, edit any tenant's schedule name, subdomain and contact details, and release or restore a squatted subdomain</li>
            <li><a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Stripe Integration</a> - Keys, webhooks and Stripe Connect for both subscription billing and ticket payments</li>
        </ul>
    </section>
</x-docs-page>
