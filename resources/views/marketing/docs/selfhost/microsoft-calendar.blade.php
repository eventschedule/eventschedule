<x-marketing-layout>
    <x-slot name="title">Outlook Calendar Integration Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Outlook Calendar</x-slot>
    <x-slot name="description">Set up bidirectional Outlook Calendar sync with Event Schedule using Microsoft Graph. Automatically sync events between both platforms.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Outlook Calendar Integration Documentation - Event Schedule",
        "description": "Set up bidirectional Outlook Calendar sync with Event Schedule using Microsoft Graph. Automatically sync events between both platforms.",
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
        },
        "datePublished": "2026-07-14",
        "dateModified": "2026-07-14"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <style {!! nonce_attr() !!}>
        .text-gradient-docs {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-docs {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>


    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden noise border-b border-gray-200 dark:border-white/5">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.22), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 80% 70%, rgba(14, 165, 233, 0.18), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-rays absolute inset-0"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Outlook Calendar" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="es-fade-up es-d-1 flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Outlook Calendar <span class="text-gradient-docs">Integration</span></h1>
            </div>
            <p class="es-fade-up es-d-2 text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up and use the Microsoft 365 / Outlook Calendar integration for bidirectional sync between Event Schedule and Outlook through Microsoft Graph.
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
                        <a href="#prerequisites" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Prerequisites</a>
                        <a href="#setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Setup Instructions</a>
                        <a href="#features" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Features</a>
                        <a href="#usage" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Usage</a>
                        <a href="#api-endpoints" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">API Endpoints</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                        <a href="#security" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Security Considerations</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Prerequisites -->
                        <section id="prerequisites" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                                Prerequisites
                            </h2>
                            <ol class="doc-list doc-list-numbered">
                                <li>A Microsoft Entra ID (Azure AD) tenant or an Azure account to register an application</li>
                                <li>Access to the Azure Portal to create an app registration</li>
                                <li>The application deployed at a public HTTPS URL (needed for the OAuth redirect and for Microsoft Graph webhook change-notifications)</li>
                            </ol>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">No public URL?</div>
                                <p>Installs without a public HTTPS URL still work. Instead of near-real-time webhooks, inbound changes are picked up by the 15-minute <code class="doc-inline-code">microsoft:sync</code> polling fallback.</p>
                            </div>

                            <div class="doc-callout doc-callout-warning mt-6">
                                <div class="doc-callout-title">Queue worker required for webhooks</div>
                                <p>For near-real-time webhooks, run an asynchronous queue (set <code class="doc-inline-code">QUEUE_CONNECTION=database</code> and keep <code class="doc-inline-code">php artisan queue:work</code> running). Inbound sync is dispatched to the queue so Microsoft Graph gets a fast response. On the default <code class="doc-inline-code">sync</code> connection the sync runs inside the webhook request, which can be slow enough that Graph deprovisions the subscription. Without a worker, inbound changes still arrive via the 15-minute poll.</p>
                            </div>
                        </section>

                        <!-- Setup Instructions -->
                        <section id="setup" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
                                </svg>
                                Setup Instructions
                            </h2>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">1. Azure App Registration</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://portal.azure.com/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Azure Portal</a> and open <strong class="text-gray-900 dark:text-white">Microsoft Entra ID</strong> &gt; <strong class="text-gray-900 dark:text-white">App registrations</strong> &gt; <strong class="text-gray-900 dark:text-white">New registration</strong></li>
                                <li>Enter a name for the application (for example, "Event Schedule")</li>
                                <li>Under <strong class="text-gray-900 dark:text-white">Supported account types</strong>, choose "Accounts in any organizational directory and personal Microsoft accounts" (this matches <code class="doc-inline-code">MICROSOFT_TENANT=common</code>)</li>
                                <li>Under <strong class="text-gray-900 dark:text-white">Redirect URI</strong>, select the <strong class="text-gray-900 dark:text-white">Web</strong> platform and enter: <code class="doc-inline-code">{APP_URL}/microsoft-calendar/callback</code></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Register</strong></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">2. API Permissions</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In the app registration, open <strong class="text-gray-900 dark:text-white">API permissions</strong> &gt; <strong class="text-gray-900 dark:text-white">Add a permission</strong> &gt; <strong class="text-gray-900 dark:text-white">Microsoft Graph</strong> &gt; <strong class="text-gray-900 dark:text-white">Delegated permissions</strong></li>
                                <li>Add the following delegated permissions:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li><code class="doc-inline-code">Calendars.ReadWrite</code></li>
                                        <li><code class="doc-inline-code">offline_access</code></li>
                                        <li><code class="doc-inline-code">openid</code></li>
                                        <li><code class="doc-inline-code">email</code></li>
                                        <li><code class="doc-inline-code">profile</code></li>
                                    </ul>
                                </li>
                                <li>If your tenant requires it, grant admin consent for the permissions</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">3. Client Secret and Client ID</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Open <strong class="text-gray-900 dark:text-white">Certificates &amp; secrets</strong> &gt; <strong class="text-gray-900 dark:text-white">New client secret</strong>, then copy the secret <strong class="text-gray-900 dark:text-white">Value</strong> immediately (it is only shown once)</li>
                                <li>Open the <strong class="text-gray-900 dark:text-white">Overview</strong> page and copy the <strong class="text-gray-900 dark:text-white">Application (client) ID</strong></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">4. Environment Configuration</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">MICROSOFT_CLIENT_ID</span>=<span class="code-string">your_application_client_id</span>
<span class="code-variable">MICROSOFT_CLIENT_SECRET</span>=<span class="code-string">your_client_secret_value</span>
<span class="code-variable">MICROSOFT_REDIRECT_URI</span>=<span class="code-string">https://your-domain.com/microsoft-calendar/callback</span>
<span class="code-variable">MICROSOFT_TENANT</span>=<span class="code-string">common</span>
<span class="code-variable">MICROSOFT_WEBHOOK_SECRET</span>=<span class="code-string">a_long_random_string</span></code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Variable Reference</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">MICROSOFT_CLIENT_ID</code></td>
                                            <td>The Application (client) ID from the app registration Overview page</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MICROSOFT_CLIENT_SECRET</code></td>
                                            <td>The client secret Value created under Certificates &amp; secrets</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MICROSOFT_REDIRECT_URI</code></td>
                                            <td>Must exactly match the redirect URI registered in Azure (<code class="doc-inline-code">{APP_URL}/microsoft-calendar/callback</code>)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MICROSOFT_TENANT</code></td>
                                            <td>Use <code class="doc-inline-code">common</code> for multi-tenant plus personal accounts, or your specific tenant id for a single-tenant app</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code></td>
                                            <td>Required for near-real-time webhooks (public HTTPS URL); not needed for polling-only installs. Any long random string. It is the <code class="doc-inline-code">clientState</code> that authenticates inbound Microsoft Graph notifications</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-warning mt-6">
                                <div class="doc-callout-title">Webhook secret is required</div>
                                <p>Set <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> to a long random value. Microsoft Graph echoes it back as the <code class="doc-inline-code">clientState</code> on every change notification, and Event Schedule rejects any notification whose value does not match.</p>
                            </div>
                        </section>

                        <!-- Features -->
                        <section id="features" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Features
                            </h2>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Features</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Connect Outlook Calendar:</strong> Each user connects their Microsoft account through OAuth from Settings &gt; Outlook Calendar</li>
                                <li><strong class="text-gray-900 dark:text-white">Per-Schedule Sync Direction:</strong> Choose To Outlook, From Outlook, or Both on the schedule's Integrations &gt; Outlook Calendar tab</li>
                                <li><strong class="text-gray-900 dark:text-white">Two-Way Sync:</strong> Events stay in sync between Event Schedule and Outlook</li>
                                <li><strong class="text-gray-900 dark:text-white">Teams Meeting Links:</strong> Optionally add a Microsoft Teams join link to online events through a per-schedule toggle</li>
                                <li><strong class="text-gray-900 dark:text-white">Near-Real-Time Inbound:</strong> Microsoft Graph subscriptions push Outlook changes back to Event Schedule</li>
                                <li><strong class="text-gray-900 dark:text-white">Polling Fallback:</strong> A 15-minute <code class="doc-inline-code">microsoft:sync</code> command catches anything webhooks miss</li>
                                <li><strong class="text-gray-900 dark:text-white">Subscription Renewal:</strong> A daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command renews Graph subscriptions, which expire about every 2.5 days</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Information Synced</h3>
                            <ul class="doc-list mb-6">
                                <li>Event name</li>
                                <li>Event description (with venue and URL information)</li>
                                <li>Start and end times</li>
                                <li>Location (venue address)</li>
                                <li>Microsoft Teams join link (for online events when enabled)</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sync Direction</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Each schedule can choose one of three sync directions:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Direction</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">To Outlook</span></td>
                                            <td>Event Schedule events are pushed to the connected Outlook calendar</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">From Outlook</span></td>
                                            <td>Outlook events are imported into Event Schedule</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Both</span></td>
                                            <td>Changes flow in both directions and stay in sync</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Microsoft Teams Meeting Links</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">When the Teams toggle is enabled for a schedule, events without a physical venue (online events) get a Microsoft Teams meeting created through Graph, and the join link is written back to the event.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Real-Time Sync and Polling Fallback</h3>
                            <ul class="doc-list">
                                <li>Microsoft Graph subscriptions deliver change notifications to the webhook endpoint for near-real-time inbound sync</li>
                                <li>The 15-minute <code class="doc-inline-code">microsoft:sync</code> command polls for changes as a fallback, and is the primary path when no public URL is available</li>
                                <li>The daily <code class="doc-inline-code">microsoft:refresh-webhooks</code> command renews subscriptions before they expire (about every 2.5 days)</li>
                            </ul>
                        </section>

                        <!-- Usage -->
                        <section id="usage" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                                </svg>
                                Usage
                            </h2>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">For Users</h3>

                            <div class="space-y-6 mb-8">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">1. Connect Outlook Calendar</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Go to your settings page (<code class="doc-inline-code">/settings</code>)</li>
                                        <li>Find the "Outlook Calendar" section</li>
                                        <li>Click "Connect Outlook Calendar"</li>
                                        <li>Authorize the application in the Microsoft sign-in flow</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">2. Choose a Calendar and Sync Direction</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Open the schedule's Integrations tab</li>
                                        <li>Select the "Outlook Calendar" tab</li>
                                        <li>Pick which Outlook calendar to sync with</li>
                                        <li>Choose the sync direction: To Outlook, From Outlook, or Both</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">3. Push Events to Outlook</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>Create or publish events as usual</li>
                                        <li>While connected, events are pushed to the selected Outlook calendar automatically</li>
                                    </ol>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">4. Enable Teams Meeting Links</h4>
                                    <ol class="doc-list doc-list-numbered text-sm">
                                        <li>On the schedule's Outlook Calendar tab, enable the Microsoft Teams toggle</li>
                                        <li>Online (venue-less) events now get a Teams join link written to the event</li>
                                    </ol>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Automatic Sync</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Once a schedule is connected, events are synced automatically when:</p>
                            <ul class="doc-list mb-6">
                                <li>Created (if the user has Outlook Calendar connected)</li>
                                <li>Updated (if the user has Outlook Calendar connected)</li>
                                <li>Deleted (if the event was previously synced)</li>
                            </ul>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">Scheduled Sync</div>
                                <p>Inbound polling and subscription renewal run through scheduled commands (<code class="doc-inline-code">microsoft:sync</code> every 15 minutes and <code class="doc-inline-code">microsoft:refresh-webhooks</code> daily). Make sure the Laravel scheduler cron is active: <code class="doc-inline-code">* * * * * php artisan schedule:run</code></p>
                            </div>
                        </section>

                        <!-- API Endpoints -->
                        <section id="api-endpoints" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                API Endpoints
                            </h2>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/redirect</code></td>
                                            <td>Start OAuth flow</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/callback</code></td>
                                            <td>OAuth callback</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/reauthorize</code></td>
                                            <td>Re-run consent to obtain a refresh token</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/disconnect</code></td>
                                            <td>Disconnect Outlook Calendar</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/calendars</code></td>
                                            <td>Get user's calendars</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /microsoft-calendar/sync/{subdomain}</code></td>
                                            <td>Sync a schedule's events</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /microsoft-calendar/sync-event/{subdomain}/{eventId}</code></td>
                                            <td>Sync a specific event</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">DELETE /microsoft-calendar/unsync-event/{subdomain}/{eventId}</code></td>
                                            <td>Remove event from Outlook</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /microsoft-calendar/webhook</code></td>
                                            <td>Microsoft Graph subscription validation handshake</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /microsoft-calendar/webhook</code></td>
                                            <td>Microsoft Graph change notifications (also handles the validation handshake)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scheduled Commands</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">These Artisan commands keep inbound sync and Graph subscriptions healthy:</p>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Command</th>
                                            <th>Frequency</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">microsoft:sync</code></td>
                                            <td>Every 15 minutes</td>
                                            <td>Polls Outlook for changes (inbound sync fallback)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">microsoft:refresh-webhooks</code></td>
                                            <td>Daily</td>
                                            <td>Renews Microsoft Graph subscriptions before they expire</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">These commands run through the Laravel scheduler, which requires the following cron entry:</p>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>crontab</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>* * * * * php artisan schedule:run</code></pre>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Common Issues</h3>

                            <div class="space-y-4 mb-8">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">No refresh token / repeated re-authentication</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Disconnect and reconnect the account</li>
                                        <li>Make sure the <code class="doc-inline-code">offline_access</code> scope is granted in the app registration</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Teams meeting link not created</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Personal Microsoft accounts may not support Teams for Business meetings</li>
                                        <li>In that case the app falls back to creating a normal event without a Teams link</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Inbound changes not updating</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Confirm the app has a public HTTPS URL so Graph can reach the webhook endpoint</li>
                                        <li>Without a public URL, rely on the 15-minute <code class="doc-inline-code">microsoft:sync</code> poll and confirm the scheduler cron is running</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Subscription creation fails</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Ensure <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> is set</li>
                                        <li>Ensure the notification URL is publicly reachable over HTTPS</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logs</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Sync operations are logged in the application logs. Check <code class="doc-inline-code">storage/logs/laravel.log</code> for detailed information about sync operations.</p>
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
                                <li><span class="font-semibold text-gray-900 dark:text-white">Token Storage:</span> Microsoft OAuth tokens are stored encrypted at rest, per user</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Rotating Refresh Tokens:</span> Microsoft rotates the refresh token on each refresh, and the app stores the latest one</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Webhook Authentication:</span> <code class="doc-inline-code">MICROSOFT_WEBHOOK_SECRET</code> (the <code class="doc-inline-code">clientState</code>) authenticates inbound Graph notifications, and mismatched notifications are rejected</li>
                                <li><span class="font-semibold text-gray-900 dark:text-white">Secrets in .env:</span> Keep the client secret and webhook secret in <code class="doc-inline-code">.env</code>, and never commit them to source control</li>
                            </ol>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
