<x-docs-page
    key="saas/custom-domains"
    description="Let the schedules on your SaaS deployment use their own domain names, with automatic SSL provisioning through DigitalOcean App Platform."
    lede="Let the schedules on your deployment use their own domain names, with automatic SSL provisioning via DigitalOcean App Platform."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-link href="#environment">Environment Setup</x-doc-nav-link>
        <x-doc-nav-link href="#how-it-works">How It Works</x-doc-nav-link>
        <x-doc-nav-link href="#dns-setup">DNS Setup</x-doc-nav-link>
        <x-doc-nav-link href="#admin-management">Admin Management</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
            <x-doc-badge plan="enterprise" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A custom domain lets a schedule be reached at its owner's own address (for example <code class="doc-inline-code">events.example.com</code>) instead of the default <code class="doc-inline-code">subdomain.yourdomain.com</code> URL.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Enterprise only</div>
            <p>Custom domains are an Enterprise feature. On a schedule that is not on Enterprise, the Redirect and Direct options render disabled with an upgrade prompt, and the server re-applies the schedule's existing values on save, so a hand-crafted POST cannot set a domain either. A schedule that later drops off Enterprise keeps the domain it already has: only <em>changing</em> it is blocked. Remove it from the admin panel if you need it gone.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Owners choose the mode themselves. In the schedule's settings, on the <strong class="text-gray-900 dark:text-white">General</strong> tab, the <strong class="text-gray-900 dark:text-white">Schedule URL</strong> has an <strong class="text-gray-900 dark:text-white">Edit</strong> button that reveals a <strong class="text-gray-900 dark:text-white">Mode</strong> chooser with three options.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Mode</th>
                        <th>What visitors get</th>
                        <th>Canonical URL</th>
                        <th>Requires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Subdomain</strong></td>
                        <td>The default. The schedule is served at <code class="doc-inline-code">subdomain.yourdomain.com</code> and no custom domain is stored.</td>
                        <td>The subdomain</td>
                        <td>Nothing</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Redirect</strong></td>
                        <td>The owner points their domain at Cloudflare, which 301 redirects every request to the schedule URL.</td>
                        <td>The subdomain</td>
                        <td>Enterprise</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Direct</strong></td>
                        <td>The schedule is served on the owner's domain itself, over HTTPS, with the domain kept in the address bar.</td>
                        <td>The custom domain, once active</td>
                        <td>Enterprise plus DigitalOcean App Platform</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">What changes on a direct custom domain</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Direct mode is not only a different address. Once the domain is active, several parts of the product behave differently on that host:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">The custom domain becomes the SEO canonical</strong> for that schedule's guest portal, and the schedule serves its own <code class="doc-inline-code">/sitemap.xml</code> on that host. Your platform's global sitemap never lists custom-domain URLs. In Redirect mode the subdomain stays canonical, because the custom domain only 301s away from itself.</li>
            <li><strong class="text-gray-900 dark:text-white">Ads are never served.</strong> If you run AdSense on free schedules, ad slots are suppressed on any custom domain, whatever the schedule's plan. Serving ads on a domain you do not own would breach AdSense policy.</li>
            <li><strong class="text-gray-900 dark:text-white">The accommodation map only runs on the owner's own affiliate ID.</strong> Your instance-wide Stay22 fallback ID is never used on a customer's custom domain, so the map simply does not render for a schedule that has not set its own ID.</li>
            <li><strong class="text-gray-900 dark:text-white">The embedded Google map is omitted</strong> on event pages served from a custom domain, so your Maps API key is never handed out on a host you do not control. The address and its link are still shown.</li>
            <li><strong class="text-gray-900 dark:text-white">Sign-in, admin and follow links stay on the app subdomain.</strong> Only the schedule's own URLs are rewritten to the custom domain, so the session cookie keeps working.</li>
        </ul>
    </section>

    <!-- Prerequisites -->
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>

        <h3 class="doc-subheading">Both modes</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Hosted mode</strong> - your deployment must run with <code class="doc-inline-code">IS_HOSTED=true</code>. The mode chooser is only rendered in hosted mode, and the request-time domain lookup is skipped entirely otherwise. A selfhosted install is path-routed on the one domain you point at it, so it has nothing to configure here.</li>
            <li><strong class="text-gray-900 dark:text-white">An Enterprise schedule</strong> - the plan gate applies per schedule, not per deployment.</li>
        </ul>

        <h3 class="doc-subheading">Direct mode only</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">DigitalOcean App Platform</strong> - your app must be deployed on DigitalOcean App Platform, which is what provisions and renews the SSL certificate for each customer domain.</li>
            <li><strong class="text-gray-900 dark:text-white">DO API token</strong> - a DigitalOcean personal access token scoped to <strong>read</strong> and <strong>update</strong> on the <strong>app</strong> resource. Adding and removing a domain is a read of the current app spec followed by a write of the amended one.</li>
            <li><strong class="text-gray-900 dark:text-white">App ID</strong> - the ID of your DigitalOcean App Platform app.</li>
            <li><strong class="text-gray-900 dark:text-white">App hostname</strong> - the app's <code class="doc-inline-code">.ondigitalocean.app</code> starter domain, which is the CNAME target customers point at.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Redirect mode needs no server-side configuration at all. Owners set up their own Cloudflare redirect, and nothing is registered with DigitalOcean. It is still Enterprise-gated, so it is not a way around the plan.</p>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Direct mode is hidden until it is configured</div>
            <p>The <strong>Direct</strong> option only appears in schedule settings when <code class="doc-inline-code">DO_APP_HOSTNAME</code> is set, because the CNAME instructions have nothing to show without it. Until you set it, Enterprise schedules see only Subdomain and Redirect.</p>
        </div>
    </section>

    <!-- Environment Setup -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Environment Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following variables to your <code class="doc-inline-code">.env</code> file:</p>

        <pre class="doc-code-block"><code>DO_API_TOKEN=your_digitalocean_api_token
DO_APP_ID=your_app_id
DO_APP_HOSTNAME=your-app.ondigitalocean.app</code></pre>

        <div class="space-y-4 mb-6 mt-6">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">DO_API_TOKEN</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your DigitalOcean personal access token. Generate one at <code class="doc-inline-code">cloud.digitalocean.com/account/api/tokens</code>. Select <strong>Custom Scopes</strong>, then expand the <strong>app</strong> resource and check <strong>read</strong> and <strong>update</strong>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">DO_APP_ID</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your App Platform app ID. Find it in the DigitalOcean dashboard URL: <code class="doc-inline-code">cloud.digitalocean.com/apps/YOUR_APP_ID</code>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">DO_APP_HOSTNAME</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The starter domain of your DigitalOcean app. Find it under <strong>Settings</strong> &gt; <strong>Domains</strong>, it ends in <code class="doc-inline-code">.ondigitalocean.app</code>. Customers will create CNAME records pointing to this value.</p>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The three values are read once into the <code class="doc-inline-code">digitalocean</code> block of <code class="doc-inline-code">config/services.php</code>, and they do different jobs:</p>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">DO_API_TOKEN</code> and <code class="doc-inline-code">DO_APP_ID</code> together decide whether provisioning runs at all. If either is missing, saving a domain, re-provisioning and the status sync all quietly no-op, and the domain never leaves the pending state.</li>
            <li><code class="doc-inline-code">DO_APP_HOSTNAME</code> decides whether Direct mode is offered to owners, and is the value shown in the copy-to-clipboard CNAME instructions.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>If you have run <code class="doc-inline-code">php artisan config:cache</code>, re-run it (or <code class="doc-inline-code">php artisan config:clear</code>) after editing <code class="doc-inline-code">.env</code> so the new values take effect.</p>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            How It Works
        </h2>

        <h3 class="doc-subheading">Redirect Mode</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The owner stores their domain and sets up a Cloudflare page rule that 301 redirects it to their <code class="doc-inline-code">subdomain.yourdomain.com</code> URL. Nothing is registered with DigitalOcean, no certificate is issued by you, and no status is tracked, so the Status column in the admin panel stays empty for these schedules. The subdomain remains the canonical URL, since the custom domain never renders a page itself.</p>

        <h3 class="doc-subheading">Direct Mode</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When an owner saves their domain in Direct mode:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>The domain is normalized to <code class="doc-inline-code">https://host</code>, its hostname is stored separately for fast lookup, and it is rejected if another schedule already claims it or if the hostname contains <code class="doc-inline-code">eventschedule.com</code>.</li>
            <li>The hostname is added to your DigitalOcean App Platform app spec over the API, and the schedule's domain status is set to <strong class="text-gray-900 dark:text-white">pending</strong> (or <strong class="text-gray-900 dark:text-white">failed</strong> if the API call did not succeed).</li>
            <li>The owner adds a CNAME record pointing at your app's hostname.</li>
            <li>DigitalOcean verifies the record and provisions an SSL certificate.</li>
            <li>A scheduled sync notices the domain has gone live and flips the status to <strong class="text-gray-900 dark:text-white">active</strong>.</li>
            <li>From then on, requests arriving on the custom domain are matched to the schedule and served, and the schedule's own subdomain URLs in the response are rewritten to the custom domain.</li>
        </ol>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Only the schedule's own URLs are rewritten</div>
            <p>The rewrite deliberately leaves app URLs such as login, the admin panel and the follow flow pointing at <code class="doc-inline-code">app.yourdomain.com</code>. Those pages need the session cookie that is scoped to your base domain, so moving them onto the customer's host would sign the visitor out. Redirects issued during the structured guest-submit flow opt out of the rewrite for the same reason.</p>
        </div>

        <h3 class="doc-subheading">Domain status lifecycle</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Direct-mode domains carry a status. Owners and admins see a coloured badge; the underlying value is what filters and the middleware key off.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Stored value</th>
                        <th>Badge label</th>
                        <th>Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">pending</code></td>
                        <td>Setting up...</td>
                        <td>Registered with DigitalOcean, waiting on DNS and the certificate. The domain does not serve the schedule yet.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">active</code></td>
                        <td>Active</td>
                        <td>Live. This is the only state in which the custom domain serves the schedule and becomes its canonical URL.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">failed</code></td>
                        <td>Setup failed</td>
                        <td>The API call failed, or the domain is no longer present in the app spec. Re-provisioning from the admin panel puts it back to pending.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The transition out of pending is done by a scheduled command that runs <strong class="text-gray-900 dark:text-white">every five minutes</strong> on hosted deployments:</p>

        <pre class="doc-code-block"><code>php artisan app:sync-domain-statuses</code></pre>

        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">It reads every domain on your app from the DigitalOcean API and, for each schedule still pending, marks it active once DigitalOcean reports the domain as live, or failed if the domain is no longer on the app at all. If the API returns nothing at all it stops rather than marking everything failed, so an API outage cannot take working domains offline. It needs your cron entry (<code class="doc-inline-code">* * * * * php artisan schedule:run</code>) to be running.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Technical Detail</div>
            <p>Incoming requests are handled by the <code class="doc-inline-code">ResolveCustomDomain</code> middleware, which runs before everything else. It looks the host up against active direct-mode schedules, caches the result for 10 minutes, and rewrites the HTTP Host header to the schedule's subdomain so the existing subdomain routes match with no route changes. It also nulls the session cookie domain so the cookie is scoped to the customer's origin, then rewrites the schedule's subdomain URLs to the custom domain in HTML bodies, JSON bodies and redirect <code class="doc-inline-code">Location</code> headers. An unknown host gets a 404.</p>
        </div>
    </section>

    <!-- DNS Setup -->
    <section id="dns-setup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            DNS Setup for Customers
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When an owner selects Direct mode, the settings page shows these steps and the exact hostname to copy. They need to create one CNAME record at their domain registrar:</p>

        <div class="doc-table-wrap">
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

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Log in to the domain registrar (e.g. GoDaddy, Namecheap, Cloudflare).</li>
            <li>Open the DNS settings for the domain.</li>
            <li>Add the CNAME record above.</li>
            <li>Save, and wait for DNS to propagate. SSL is provisioned automatically once DigitalOcean can resolve the record.</li>
        </ol>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Apex domains need CNAME flattening</div>
            <p>A plain CNAME is not valid at the apex of a zone. If the owner wants <code class="doc-inline-code">example.com</code> rather than <code class="doc-inline-code">events.example.com</code>, their DNS provider has to support CNAME flattening, ALIAS or ANAME records (Cloudflare and several registrars do). A subdomain such as <code class="doc-inline-code">events</code> avoids the problem entirely and is the easier recommendation.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>DNS propagation can take up to 48 hours, though it is usually much faster. The status badge on the schedule URL moves from <strong>Setting up...</strong> to <strong>Active</strong> within five minutes of DigitalOcean reporting the domain live, and the same change shows in the admin domains list.</p>
        </div>
    </section>

    <!-- Admin Management -->
    <section id="admin-management" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Admin Management
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every schedule with a custom domain, in either mode, is listed at <code class="doc-inline-code">/admin/domains</code>, reached from <strong class="text-gray-900 dark:text-white">Manage</strong> &gt; <strong class="text-gray-900 dark:text-white">Domains</strong> in the admin navigation. The tab is hosted-only and admin-only.</p>

        <h3 class="doc-subheading">Reading the page</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Summary cards</strong> - Total (every schedule with a custom domain), Direct, Active and Setting up... The last three count direct-mode schedules only.</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filters</strong> - search by schedule name, subdomain or domain, and filter by mode or by status.</li>
            <li><strong class="text-gray-900 dark:text-white">Domain table</strong> - Schedule, Custom Domain, Mode, Status and DO Status, 20 rows to a page. <strong class="text-gray-900 dark:text-white">DO Status</strong> is the live phase read straight from the DigitalOcean API on page load, so it is the column to trust when the stored status looks wrong. It is blank if the API is unreachable or unconfigured.</li>
        </ul>

        <h3 class="doc-subheading">Actions</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Re-provision</strong> - removes and re-adds the domain to DigitalOcean and resets the status to pending. Use it when SSL provisioning gets stuck or after fixing a bad DNS record. Direct mode only.</li>
            <li><strong class="text-gray-900 dark:text-white">Remove</strong> - removes the domain from DigitalOcean and clears the schedule's domain, mode, host and status, sending it back to its subdomain URL. This is also how you take a domain off a schedule that has dropped off Enterprise.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Both actions are written to the audit log, and both clear the middleware's cached lookup, so the change takes effect on the next request rather than after 10 minutes.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">You do not have to go looking</div>
            <p>Domains sitting in <strong>Setting up...</strong> or <strong>Setup failed</strong> raise a badge on the Domains tab and an entry in the admin panel's "Needs attention" list, each linking straight to the filtered list. Deleting a schedule outright also removes its domain from DigitalOcean.</p>
        </div>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Domain stuck on "Setting up..."</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Check the DO Status column first. If it is blank the domain was never registered on your app, and by far the most common reason is DNS: DigitalOcean will not accept a domain whose CNAME does not resolve to your app yet. Verify the record before anything else, remembering propagation can take up to 48 hours. A blank column can also mean the API credentials are missing or wrong. If it shows a phase but the stored status has not moved, confirm your scheduler is running, since only <code class="doc-inline-code">app:sync-domain-statuses</code> promotes a domain to active.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">SSL certificate not provisioning</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">DigitalOcean requires the CNAME to be resolvable before issuing an SSL certificate. Ensure there are no conflicting A or AAAA records for the domain. If using Cloudflare DNS, disable the proxy (orange cloud) for the CNAME record, otherwise DigitalOcean sees Cloudflare's addresses instead of your app.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Domain shows "Setup failed"</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Either the API call failed at save time, or the sync no longer finds the domain on your app. DigitalOcean's own reason is shown directly under the Setup failed badge in the admin panel, so start there. Common causes: a CNAME that does not resolve yet, an invalid hostname, the domain already registered against another app, or an API token missing the <strong>update</strong> scope. Fix the cause, then re-provision. If re-provisioning reports the domain is already registered, the entry is present and correct on your app, so the remaining fix is the DNS record or removing the domain and adding it back.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">404 on the custom domain</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">An unknown host is a 404 by design. The middleware only serves a schedule whose mode is direct <em>and</em> whose status is active, so a pending or redirect-mode domain 404s here. Lookups are cached for 10 minutes, so a status that has only just changed can take that long to take effect unless it was changed from the admin panel, which clears the cache.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Domain already taken" when saving</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A hostname can belong to one schedule only, and the comparison is on the hostname, so <code class="doc-inline-code">http://</code> and <code class="doc-inline-code">https://</code> forms of the same address collide. Find the other schedule in the admin domains list and remove the domain there first. Separately, any hostname containing <code class="doc-inline-code">eventschedule.com</code> is rejected outright.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Owner-only actions return 405</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Owner and admin actions live on the app subdomain, not on the guest host. If you have customised a guest page, any owner-facing link or form there must be built with <code class="doc-inline-code">app_url()</code> around a relative route. A bare <code class="doc-inline-code">route()</code> posts to the guest host, gets 302 redirected to the app subdomain, and the redirect downgrades the POST to a GET, which the POST-only route answers with 405.</p>
            </div>
        </div>
    </section>
</x-docs-page>
