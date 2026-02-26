<x-marketing-layout>
    <x-slot name="title">Custom Domains for SaaS - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Custom Domains</x-slot>
    <x-slot name="description">Configure custom domain support for your SaaS Event Schedule deployment using DigitalOcean App Platform for automatic SSL provisioning.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Custom Domains for SaaS - Event Schedule",
        "description": "Configure custom domain support for your SaaS Event Schedule deployment using DigitalOcean App Platform for automatic SSL provisioning.",
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
        "datePublished": "2026-02-01",
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Custom Domains" section="saas" sectionTitle="SaaS" sectionRoute="marketing.docs" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Custom Domains</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Enable your customers to use their own domain names with automatic SSL provisioning via DigitalOcean App Platform.
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
                        <a href="#prerequisites" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Prerequisites</a>
                        <a href="#environment" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Environment Setup</a>
                        <a href="#how-it-works" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">How It Works</a>
                        <a href="#dns-setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">DNS Setup</a>
                        <a href="#admin-management" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Admin Management</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Custom domains allow your customers to use their own domain names (e.g., <code class="doc-inline-code">events.example.com</code>) instead of the default <code class="doc-inline-code">subdomain.yourdomain.com</code> URL.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">Two modes are available:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Mode</th>
                                            <th>How It Works</th>
                                            <th>Best For</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong class="text-gray-900 dark:text-white">Redirect</strong></td>
                                            <td>Customer sets up Cloudflare to 301 redirect their domain to the schedule URL</td>
                                            <td>Simple setup, no server configuration needed</td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-gray-900 dark:text-white">Direct</strong></td>
                                            <td>Schedule is served directly on the customer's domain with automatic SSL</td>
                                            <td>Seamless experience, domain stays in the address bar</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Prerequisites -->
                        <section id="prerequisites" class="doc-section">
                            <h2 class="doc-heading">Prerequisites</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Direct mode requires:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">DigitalOcean App Platform</strong> - Your app must be deployed on DigitalOcean App Platform, which handles SSL provisioning for custom domains.</li>
                                <li><strong class="text-gray-900 dark:text-white">DO API Token</strong> - A DigitalOcean personal access token with read/write access to Apps.</li>
                                <li><strong class="text-gray-900 dark:text-white">App ID</strong> - Your DigitalOcean App Platform app ID.</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Redirect mode does not require any server-side configuration. Customers set up their own Cloudflare redirect rules.</p>
                            </div>
                        </section>

                        <!-- Environment Setup -->
                        <section id="environment" class="doc-section">
                            <h2 class="doc-heading">Environment Setup</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <pre class="doc-code-block"><code>DO_API_TOKEN=your_digitalocean_api_token
DO_APP_ID=your_app_id
DO_APP_HOSTNAME=your-app.ondigitalocean.app</code></pre>

                            <div class="space-y-4 mb-6 mt-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">DO_API_TOKEN</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your DigitalOcean personal access token. Generate one at <code class="doc-inline-code">cloud.digitalocean.com/account/api/tokens</code>. Select <strong>Custom Scopes</strong>, then expand the <strong>app</strong> resource and check <strong>read</strong> and <strong>update</strong>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">DO_APP_ID</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your App Platform app ID. Find it in the DigitalOcean dashboard URL: <code class="doc-inline-code">cloud.digitalocean.com/apps/YOUR_APP_ID</code>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">DO_APP_HOSTNAME</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">The starter domain of your DigitalOcean app. Find it under <strong>Settings</strong> &gt; <strong>Domains</strong>, it ends in <code class="doc-inline-code">.ondigitalocean.app</code>. Customers will create CNAME records pointing to this value.</p>
                                </div>
                            </div>
                        </section>

                        <!-- How It Works -->
                        <section id="how-it-works" class="doc-section">
                            <h2 class="doc-heading">How It Works</h2>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Redirect Mode</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">In redirect mode, the customer sets up a Cloudflare page rule that 301 redirects their domain to their <code class="doc-inline-code">subdomain.yourdomain.com</code> URL. No server-side changes are needed.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-6">Direct Mode</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When a customer saves their domain in Direct mode:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>The domain is registered with DigitalOcean App Platform via API</li>
                                <li>The customer adds a CNAME record pointing to your app's hostname</li>
                                <li>DigitalOcean automatically provisions an SSL certificate</li>
                                <li>Incoming requests on the custom domain are routed to the correct schedule via middleware</li>
                                <li>All URLs in the HTML output are rewritten to use the custom domain</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Technical Detail</div>
                                <p>The <code class="doc-inline-code">ResolveCustomDomain</code> middleware rewrites the HTTP Host header so that existing subdomain-based routes match without any route changes. After the response is generated, subdomain URLs are replaced with the custom domain in the HTML output.</p>
                            </div>
                        </section>

                        <!-- DNS Setup -->
                        <section id="dns-setup" class="doc-section">
                            <h2 class="doc-heading">DNS Setup for Customers</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When a customer selects Direct mode, they need to create a CNAME record at their domain registrar:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Record Type</th>
                                            <th>Name</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>CNAME</td>
                                            <td><code class="doc-inline-code">@</code> or subdomain (e.g., <code class="doc-inline-code">events</code>)</td>
                                            <td>Your <code class="doc-inline-code">DO_APP_HOSTNAME</code> value</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>DNS propagation can take up to 48 hours. SSL provisioning happens automatically once DigitalOcean verifies the CNAME record. The domain status in the admin panel will change from "pending" to "active" once complete.</p>
                            </div>
                        </section>

                        <!-- Admin Management -->
                        <section id="admin-management" class="doc-section">
                            <h2 class="doc-heading">Admin Management</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">The <code class="doc-inline-code">/admin/domains</code> page provides a dashboard for monitoring and managing all custom domains:</p>

                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Summary cards</strong> - Total custom domains, direct mode count, active count, and pending count.</li>
                                <li><strong class="text-gray-900 dark:text-white">Domain table</strong> - Lists all schedules with custom domains, showing mode, status, and live DigitalOcean status.</li>
                                <li><strong class="text-gray-900 dark:text-white">Re-provision</strong> - Removes and re-adds a domain to DigitalOcean, useful if SSL provisioning gets stuck.</li>
                                <li><strong class="text-gray-900 dark:text-white">Remove</strong> - Removes the domain configuration from DigitalOcean and clears the mode/status fields.</li>
                            </ul>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">Troubleshooting</h2>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Domain stuck on "pending"</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Verify the CNAME record is correctly configured. DNS propagation can take up to 48 hours. If it persists beyond that, try re-provisioning from the admin panel.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">SSL certificate not provisioning</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">DigitalOcean requires the CNAME to be resolvable before issuing an SSL certificate. Ensure there are no conflicting A records for the domain. If using Cloudflare DNS, disable the proxy (orange cloud) for the CNAME record.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Domain shows "failed" status</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Check the DigitalOcean dashboard for specific error details. Common causes: invalid CNAME, domain already registered with another app, or API token permissions. Try re-provisioning after fixing the issue.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">404 error on custom domain</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">The domain must have <code class="doc-inline-code">custom_domain_status</code> set to "active" for the middleware to serve content. Check the admin panel to verify the domain status. The middleware caches domain lookups for 10 minutes.</p>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
