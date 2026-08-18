<x-docs-page
    key="selfhost/boost"
    description="Learn how to configure Meta/Facebook ads integration for Event Schedule's boost feature, enabling users to promote events through paid social media campaigns."
    lede="Configure Meta/Facebook ads integration to let users promote events through paid Facebook and Instagram campaigns."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#facebook-app">Create a Facebook App</x-doc-nav-link>
        <x-doc-nav-link href="#ad-account">Meta Business & Ad Account</x-doc-nav-link>
        <x-doc-nav-link href="#facebook-page">Facebook Page</x-doc-nav-link>
        <x-doc-nav-link href="#system-user">System User & Access Token</x-doc-nav-link>
        <x-doc-nav-link href="#pixel">Meta Pixel</x-doc-nav-link>
        <x-doc-nav-link href="#webhooks">Webhooks</x-doc-nav-link>
        <x-doc-nav-link href="#app-review">App Review</x-doc-nav-link>
        <x-doc-nav-link href="#environment">Environment Variables</x-doc-nav-link>
        <x-doc-nav-link href="#scheduled-command">Scheduled Command</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Boost lets the schedules on your instance promote events through paid Facebook and Instagram ads via the Meta Marketing API. Your instance acts as the intermediary: it creates the campaign, ad set and ad on behalf of the schedule owner using a single platform-owned Meta ad account, Facebook Page and system user token that you configure here. Nobody has to connect their own Facebook account, and there is no Facebook login step anywhere in the boost flow. For what the feature looks like from the schedule owner's side, see the <a href="{{ route('marketing.docs.boost') }}" class="doc-link">Boost user guide</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Before you start, make sure you have:</p>
        <ul class="doc-list mb-6">
            <li>A Meta Business account, with permission to create an ad account and manage a Facebook Page</li>
            <li>A payment method on that ad account. Meta bills it directly for all ad spend</li>
            <li>A publicly reachable install over HTTPS. Every ad's destination is the event's own public page, so that URL has to resolve from the public internet. It also has to be reachable in the other direction if you want webhooks (Step 6)</li>
        </ul>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">On a selfhosted install, the ad spend is yours</div>
            <p>A selfhosted install charges nothing for a boost: the campaign is marked as paid with a total of 0, and the <code class="doc-inline-code">META_MARKUP_RATE</code> service fee is forced to 0 whenever <code class="doc-inline-code">IS_HOSTED=false</code>. No Stripe configuration is involved. Meta invoices the one ad account you configure below, so every campaign any schedule on your instance creates spends your money. Your two limits are <code class="doc-inline-code">META_MAX_BUDGET</code> per campaign and <code class="doc-inline-code">META_MAX_CONCURRENT_BOOSTS</code> campaigns in flight per schedule.</p>
        </div>

        <h3 class="doc-subheading">What has to be true before a schedule can boost</h3>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Requirement</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">META_ACCESS_TOKEN</code> is set</td>
                        <td>The switch that makes the Meta channel exist. It turns on the <strong class="text-gray-900 dark:text-white">Facebook &amp; Instagram</strong> button in the boost dashboard's event picker, and it is what the scheduler checks before running <code class="doc-inline-code">boost:sync</code>. Nothing is ever sent to Meta without it.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_APP_ID</code> is set</td>
                        <td>Enables the <strong class="text-gray-900 dark:text-white">Boost Event</strong> button in the header of the event edit page. Without it the button is disabled and says "Boost requires Meta Ads to be configured."</td>
                    </tr>
                    <tr>
                        <td>The schedule is on a paid plan</td>
                        <td>Boost is a Pro feature. With <code class="doc-inline-code">IS_HOSTED=false</code> every schedule resolves to Enterprise, so this is always satisfied and no upgrade prompt appears. If you run a multi-tenant SaaS with <code class="doc-inline-code">IS_HOSTED=true</code>, the normal plan gate applies.</td>
                    </tr>
                    <tr>
                        <td>The event is published and upcoming</td>
                        <td>Draft events are refused outright, and the event picker only lists events that are upcoming or ongoing, not draft, and have a name.</td>
                    </tr>
                    <tr>
                        <td>The event has an image</td>
                        <td>Every ad creative needs one. The flyer image is used, falling back to the schedule's profile image and then the venue's. The purchase form warns when there is none, and with no image at all creating the ad on Meta fails.</td>
                    </tr>
                    <tr>
                        <td>A free concurrency slot</td>
                        <td>A schedule may have <code class="doc-inline-code">META_MAX_CONCURRENT_BOOSTS</code> Meta campaigns in flight, counting those that are active or awaiting payment. On-network promotions are counted separately, so one channel cannot starve the other.</td>
                    </tr>
                    <tr>
                        <td>A verified phone number</td>
                        <td>Hosted only. A selfhosted install never asks for phone verification before a boost.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Boost has a second channel you configure elsewhere</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The Boost section of the admin panel covers two channels. Everything on this page is the <strong class="text-gray-900 dark:text-white">Meta</strong> channel: paid ads bought from Facebook and Instagram. The other is <strong class="text-gray-900 dark:text-white">on-network promotions</strong>, where a paid schedule buys placement on the public pages of free schedules on your own instance. It shares the same campaign records and the same dashboard, but none of the variables on this page apply to it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">That is why <strong class="text-gray-900 dark:text-white">Boost</strong> can appear in the admin panel sidebar on an instance with no Meta configuration at all: the sidebar item shows when <code class="doc-inline-code">META_ACCESS_TOKEN</code> is set <em>or</em> the promotions engine is enabled. Promotions need three separate things - <code class="doc-inline-code">ADS_ENABLED=true</code> in <code class="doc-inline-code">.env</code>, the promotions engine switched on in the admin panel's monetization settings, and a multi-tenant hosted install - and they are covered in the <a href="{{ route('marketing.docs.saas.monetization') }}" class="doc-link">Monetization guide</a>. None of that is required for Meta boosts.</p>

        <p class="text-gray-600 dark:text-gray-300">The rest of this guide walks through the Facebook and Meta configuration, in the order it is easiest to do it.</p>
    </section>

    <!-- Step 1: Create a Facebook App -->
    <section id="facebook-app" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Step 1: Create a Facebook App
        </h2>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <code class="doc-inline-code">developers.facebook.com</code></li>
            <li>Click <strong class="text-gray-900 dark:text-white">My Apps</strong> then <strong class="text-gray-900 dark:text-white">Create App</strong></li>
            <li>Select <strong class="text-gray-900 dark:text-white">Other</strong> as the use case, then <strong class="text-gray-900 dark:text-white">Business</strong> as the app type</li>
            <li>Fill in the app name (e.g. "Event Schedule Boost") and contact email</li>
            <li>Once created, note the <strong class="text-gray-900 dark:text-white">App ID</strong> and <strong class="text-gray-900 dark:text-white">App Secret</strong> from App Settings > Basic</li>
            <li>Add the app to your Business Account under <strong class="text-gray-900 dark:text-white">Business Settings > Accounts > Apps</strong>, so the system user in Step 4 can generate a token against it</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_APP_ID</span>=<span class="code-string">your_app_id</span>
<span class="code-variable">META_APP_SECRET</span>=<span class="code-string">your_app_secret</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">The two values do different jobs. The <strong class="text-gray-900 dark:text-white">App ID</strong> is what the event page checks before it enables the Boost button. The <strong class="text-gray-900 dark:text-white">App Secret</strong> is used only to verify the signature on incoming Meta webhooks (Step 6); it is never sent to the Marketing API.</p>
    </section>

    <!-- Step 2: Meta Business & Ad Account -->
    <section id="ad-account" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Step 2: Meta Business & Ad Account
        </h2>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <code class="doc-inline-code">business.facebook.com</code></li>
            <li>Create a Business Account if you don't have one</li>
            <li>In <strong class="text-gray-900 dark:text-white">Business Settings > Accounts > Ad Accounts</strong>, click <strong class="text-gray-900 dark:text-white">Add > Create a new ad account</strong></li>
            <li>Name it (e.g. "Event Schedule Boost Ads"), set the currency and timezone</li>
            <li>Add a payment method to the ad account. Meta bills it directly for every campaign your instance creates</li>
            <li>Note the <strong class="text-gray-900 dark:text-white">Ad Account ID</strong> (numeric, without the <code class="doc-inline-code">act_</code> prefix - the code adds that automatically)</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_AD_ACCOUNT_ID</span>=<span class="code-string">your_ad_account_id</span></code></pre>
        </div>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Match the currency</div>
            <p>Budgets are sent to Meta as minor units with no currency attached, so Meta always spends in the ad account's own currency. Set <code class="doc-inline-code">META_DEFAULT_CURRENCY</code> to the currency you picked here, or the amounts shown in the app will be labelled in a currency Meta is not billing in. The app renders a symbol for the major currencies and falls back to the three-letter code, so an amount is never labelled in the wrong money.</p>
        </div>
    </section>

    <!-- Step 3: Facebook Page -->
    <section id="facebook-page" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            Step 3: Facebook Page
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Ads require a Facebook Page as the ad's identity (the "posted by" entity). Every ad your instance creates is published under this one Page, whichever schedule bought the boost.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Create a Facebook Page for your platform (or use an existing one)</li>
            <li>In <strong class="text-gray-900 dark:text-white">Business Settings > Accounts > Pages</strong>, add this page to your Business Account</li>
            <li>Select the page in that list to see its numeric <strong class="text-gray-900 dark:text-white">Page ID</strong></li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_PAGE_ID</span>=<span class="code-string">your_page_id</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">The Page ID is written into every ad creative as its page identity, so leaving <code class="doc-inline-code">META_PAGE_ID</code> unset does not disable boost gracefully: the campaign is created locally and then fails when the ad is pushed to Meta.</p>
    </section>

    <!-- Step 4: System User & Access Token -->
    <section id="system-user" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Step 4: System User & Access Token
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A System User provides a stable, long-lived token that doesn't expire when a personal account changes. This single token is what every campaign on your instance is created with, so treat it like a production secret.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>In <strong class="text-gray-900 dark:text-white">Business Settings > Users > System Users</strong>, click <strong class="text-gray-900 dark:text-white">Add</strong></li>
            <li>Name it (e.g. "Event Schedule API") and set the role to <strong class="text-gray-900 dark:text-white">Admin</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add Assets</strong> and assign:
                <ul class="doc-list mt-2">
                    <li>The Ad Account from Step 2 (with full control)</li>
                    <li>The Facebook Page from Step 3 (with full control)</li>
                    <li>The Pixel from Step 5, if you set one up, so server-side conversions are authorized</li>
                </ul>
            </li>
            <li>Click <strong class="text-gray-900 dark:text-white">Generate New Token</strong>, select the app from Step 1, choose the never-expiring option if you are offered one, and grant these permissions:
                <ul class="doc-list mt-2">
                    <li><code class="doc-inline-code">ads_management</code> - create/update/delete campaigns, ad sets, ads, and creatives</li>
                    <li><code class="doc-inline-code">ads_read</code> - read campaign insights, ad status, and review feedback</li>
                    <li><code class="doc-inline-code">pages_read_engagement</code> - required for creating ads using the page's identity</li>
                    <li><code class="doc-inline-code">pages_manage_ads</code> - required for creating page post ads</li>
                </ul>
            </li>
            <li>Copy the generated token (you won't be able to see it again)</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_ACCESS_TOKEN</span>=<span class="code-string">your_system_user_token</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">This variable is the master switch for the feature. With it set, <strong class="text-gray-900 dark:text-white">Boost</strong> appears in the admin panel sidebar and the <code class="doc-inline-code">boost:sync</code> scheduled command starts running. With it blank, the sidebar item is hidden and nothing is ever sent to Meta.</p>
    </section>

    <!-- Step 5: Meta Pixel -->
    <section id="pixel" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Step 5: Meta Pixel
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The pixel is <strong class="text-gray-900 dark:text-white">optional</strong>. Campaigns run without it; what you lose is conversion tracking, so ads can only be optimized and reported on by reach, impressions and clicks.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When <code class="doc-inline-code">META_PIXEL_ID</code> is set, two things happen, both scoped to events with an <em>active</em> Meta campaign. The browser pixel is injected into that event's public page, tracking a page view and a content view. And when a ticket sale for that event completes through Stripe, the server sends a Purchase conversion to Meta's Conversions API using the system user token, with the buyer's email address SHA-256 hashed rather than sent in the clear.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>In <strong class="text-gray-900 dark:text-white">Events Manager</strong> (<code class="doc-inline-code">business.facebook.com/events_manager</code>), click <strong class="text-gray-900 dark:text-white">Connect Data Sources</strong></li>
            <li>Select <strong class="text-gray-900 dark:text-white">Web</strong>, name the pixel (e.g. "Event Schedule Pixel")</li>
            <li>Note the <strong class="text-gray-900 dark:text-white">Pixel ID</strong></li>
            <li>Assign the pixel to the system user from Step 4, so its token is allowed to send server-side events</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_PIXEL_ID</span>=<span class="code-string">your_pixel_id</span></code></pre>
        </div>

        <div class="doc-callout doc-callout-warning mt-6">
            <div class="doc-callout-title">This loads third-party code for your visitors</div>
            <p>Setting this variable makes guest pages for boosted events load Facebook's script from <code class="doc-inline-code">connect.facebook.net</code>. Setting it also widens the Content Security Policy on every response, adding <code class="doc-inline-code">connect.facebook.net</code> to <code class="doc-inline-code">script-src</code> and <code class="doc-inline-code">www.facebook.com</code> to <code class="doc-inline-code">connect-src</code>, since a script-inserted tag carries no nonce. Guests of events that are not being boosted still load nothing from Facebook, and leaving the variable blank means no Facebook code is loaded and no external request is made. If you do enable it, say so in your privacy policy.</p>
        </div>
    </section>

    <!-- Step 6: Webhooks -->
    <section id="webhooks" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Step 6: Webhooks
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Webhooks are <strong class="text-gray-900 dark:text-white">optional but recommended</strong>. They let your instance hear about a completed campaign or a rejected ad within seconds. Skip them and the same information still arrives, just up to 15 minutes later when <a href="#scheduled-command" class="doc-link">the scheduled command</a> polls Meta.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the Facebook App Dashboard, go to <strong class="text-gray-900 dark:text-white">Add Product</strong> and add <strong class="text-gray-900 dark:text-white">Webhooks</strong></li>
            <li>Select the <strong class="text-gray-900 dark:text-white">Ad Account</strong> object type</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Subscribe</strong> and configure:
                <ul class="doc-list mt-2">
                    <li><strong class="text-gray-900 dark:text-white">Callback URL:</strong> <code class="doc-inline-code">https://yourdomain.com/webhooks/meta</code></li>
                    <li><strong class="text-gray-900 dark:text-white">Verify Token:</strong> A random string you choose (e.g. generate with <code class="doc-inline-code">openssl rand -hex 32</code>)</li>
                </ul>
            </li>
            <li>Subscribe to the two fields the app acts on:
                <ul class="doc-list mt-2">
                    <li><code class="doc-inline-code">campaign</code> - a campaign reported as completed is closed out locally, and its final analytics are pulled</li>
                    <li><code class="doc-inline-code">ad</code> - an approval or a rejection is recorded against that ad</li>
                </ul>
            </li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">META_WEBHOOK_VERIFY_TOKEN</span>=<span class="code-string">your_random_verify_token</span></code></pre>
        </div>

        <div class="doc-callout doc-callout-warning mt-6">
            <div class="doc-callout-title">Important</div>
            <p>Your server must be publicly accessible at the callback URL for webhook verification to succeed. The verify endpoint is <code class="doc-inline-code">GET /webhooks/meta</code> and the handler is <code class="doc-inline-code">POST /webhooks/meta</code>. Verification is refused unless <code class="doc-inline-code">META_WEBHOOK_VERIFY_TOKEN</code> is set, and every delivery is rejected unless its signature matches one computed with <code class="doc-inline-code">META_APP_SECRET</code>, so both variables must be present. Both endpoints are rate limited (10 requests a minute for the verify handshake, 60 for deliveries).</p>
        </div>

        <h3 class="doc-subheading">How a rejection is recorded</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Meta's verdict is stored per ad, as <code class="doc-inline-code">DISAPPROVED</code> in the <code class="doc-inline-code">meta_status</code> column of <code class="doc-inline-code">boost_ads</code>, together with the reason text Meta returned in <code class="doc-inline-code">meta_rejection_reason</code>. The ad's own <code class="doc-inline-code">status</code> column is the app's local lifecycle and is <em>not</em> where a rejection lands, so a query filtering rejected ads on <code class="doc-inline-code">status</code> silently returns nothing. This matters only if you inspect the tables directly; the campaign page reads both.</p>
        <p class="text-gray-600 dark:text-gray-300">Once every ad on a campaign is disapproved, the campaign moves to <strong class="text-gray-900 dark:text-white">rejected</strong> and the owner is emailed and sent a push notification. The refund on that path is hosted-only, because a selfhosted install never charged for the campaign in the first place. The two paths differ in one detail: when <code class="doc-inline-code">boost:sync</code> finds the rejection it also pauses the campaign at Meta, whereas the webhook path relies on Meta having stopped delivery itself.</p>
    </section>

    <!-- Step 7: App Review -->
    <section id="app-review" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Step 7: App Review
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">For production use, your app needs to hold access to the permissions it calls, which means going through Meta's App Review process. Note that you are not asking your users for anything: the integration only ever touches assets your own Business owns, and no schedule owner is ever sent to Facebook to grant a permission.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the App Dashboard, go to <strong class="text-gray-900 dark:text-white">App Review > Permissions and Features</strong></li>
            <li>Request approval for:
                <ul class="doc-list mt-2">
                    <li><code class="doc-inline-code">ads_management</code> - required</li>
                    <li><code class="doc-inline-code">ads_read</code> - required</li>
                    <li><code class="doc-inline-code">pages_read_engagement</code> - required</li>
                    <li><code class="doc-inline-code">pages_manage_ads</code> - required</li>
                </ul>
            </li>
            <li>Provide a detailed description of how your app uses each permission, with screenshots</li>
            <li>Submit for review</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Development Mode</div>
            <p>While in Development Mode, you can test with accounts that have a role on the app (admin/developer/tester). Switch to Live Mode once approved.</p>
        </div>
    </section>

    <!-- Step 8: Environment Variables -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Step 8: Environment Variables
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Here is the full set of environment variables for the Meta boost channel. Every one of them is read from <code class="doc-inline-code">config/services.php</code> under the <code class="doc-inline-code">meta</code> key, and the on-network promotions channel uses a completely separate set:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Required - Facebook App credentials</span>
<span class="code-variable">META_APP_ID</span>=<span class="code-string">your_app_id</span>
<span class="code-variable">META_APP_SECRET</span>=<span class="code-string">your_app_secret</span>

<span class="code-comment"># Required - System User access token</span>
<span class="code-variable">META_ACCESS_TOKEN</span>=<span class="code-string">your_system_user_access_token</span>

<span class="code-comment"># Required - Ad Account (numeric ID, without act_ prefix)</span>
<span class="code-variable">META_AD_ACCOUNT_ID</span>=<span class="code-string">your_ad_account_id</span>

<span class="code-comment"># Required - Facebook Page for ad identity</span>
<span class="code-variable">META_PAGE_ID</span>=<span class="code-string">your_page_id</span>

<span class="code-comment"># Optional - Pixel for conversion tracking</span>
<span class="code-variable">META_PIXEL_ID</span>=<span class="code-string">your_pixel_id</span>

<span class="code-comment"># Optional - Webhook verification (only if you subscribe to webhooks)</span>
<span class="code-variable">META_WEBHOOK_VERIFY_TOKEN</span>=<span class="code-string">your_random_verify_token</span>

<span class="code-comment"># Optional - API version (default: v21.0)</span>
<span class="code-variable">META_API_VERSION</span>=<span class="code-string">v21.0</span>

<span class="code-comment"># Optional - Budget settings</span>
<span class="code-variable">META_MIN_BUDGET</span>=<span class="code-value">10.00</span>          <span class="code-comment"># Minimum boost budget (default)</span>
<span class="code-variable">META_MAX_BUDGET</span>=<span class="code-value">1000.00</span>        <span class="code-comment"># Maximum boost budget (default)</span>
<span class="code-variable">META_DEFAULT_CURRENCY</span>=<span class="code-string">USD</span>      <span class="code-comment"># Match the ad account's currency</span>
<span class="code-variable">META_MAX_CONCURRENT_BOOSTS</span>=<span class="code-value">3</span>   <span class="code-comment"># Live boosts per schedule (default)</span>
</code></pre>
        </div>

        <div class="doc-table-wrap mt-6">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">META_APP_ID</code></td>
                        <td>Yes</td>
                        <td>Facebook App ID from Step 1. Enables the Boost button on the event page</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_APP_SECRET</code></td>
                        <td>Yes</td>
                        <td>Facebook App Secret from Step 1. Verifies incoming webhook signatures</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_ACCESS_TOKEN</code></td>
                        <td>Yes</td>
                        <td>System User access token from Step 4. Turns the whole feature on</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_AD_ACCOUNT_ID</code></td>
                        <td>Yes</td>
                        <td>Numeric Ad Account ID (without <code class="doc-inline-code">act_</code> prefix)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_PAGE_ID</code></td>
                        <td>Yes</td>
                        <td>Facebook Page ID used as the identity on every ad creative</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_PIXEL_ID</code></td>
                        <td>No</td>
                        <td>Meta Pixel ID. Enables the browser pixel on boosted events and server-side Purchase conversions</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_WEBHOOK_VERIFY_TOKEN</code></td>
                        <td>No</td>
                        <td>Random string for the webhook handshake. Needed only if you subscribe to webhooks</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_API_VERSION</code></td>
                        <td>No</td>
                        <td>Meta Graph API version (default: v21.0)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_MIN_BUDGET</code></td>
                        <td>No</td>
                        <td>Minimum budget per campaign, in currency units (default: 10.00)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_MAX_BUDGET</code></td>
                        <td>No</td>
                        <td>Maximum budget per campaign, in currency units (default: 1000.00). This is the cap that applies on a selfhosted install</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_DEFAULT_CURRENCY</code></td>
                        <td>No</td>
                        <td>Currency code campaigns are recorded in (default: USD). Set it to the ad account's currency</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_MAX_CONCURRENT_BOOSTS</code></td>
                        <td>No</td>
                        <td>Live campaigns allowed per schedule (default: 3). Applies on a selfhosted install; a hosted install earns its limit instead</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_MARKUP_RATE</code></td>
                        <td>No</td>
                        <td>Hosted only. Service fee added on top of the ad budget (default: 0.20). Forced to 0 when <code class="doc-inline-code">IS_HOSTED=false</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">META_BOOST_DEFAULT_LIMIT</code></td>
                        <td>No</td>
                        <td>Hosted only. Starting per-campaign spending limit for a new schedule (default: 10.00), before its limit grows with completed campaigns</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Hosted-only controls</div>
            <p>The <strong class="text-gray-900 dark:text-white">Grant Boost Credit</strong> and <strong class="text-gray-900 dark:text-white">Set Spending Limit</strong> panels under <strong class="text-gray-900 dark:text-white">Manage &gt; Boost</strong> in the admin panel, and the per-schedule limit that grows as a schedule completes campaigns, are all part of the hosted billing model. The panels are still drawn on a selfhosted install, but the values they write are never read: with <code class="doc-inline-code">IS_HOSTED=false</code> the per-campaign cap is always <code class="doc-inline-code">META_MAX_BUDGET</code> and there is nothing to charge credit against.</p>
        </div>

        <h3 class="doc-subheading">Applying the changes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">No code changes are needed - every key above is already wired up in <code class="doc-inline-code">config/services.php</code>. Clear the config cache after editing <code class="doc-inline-code">.env</code>, or the app keeps reading the old values:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">php</span> artisan config:clear</code></pre>
        </div>
    </section>

    <!-- Step 9: Scheduled Command -->
    <section id="scheduled-command" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Step 9: Scheduled Command
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Two commands keep boost campaigns in step with Meta. Both are already registered in <code class="doc-inline-code">routes/console.php</code> and run every 15 minutes, so all you have to do is make sure the Laravel scheduler is running:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>crontab</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>* * * * * php artisan schedule:run</code></pre>
        </div>

        <ul class="doc-list mt-6 mb-6">
            <li><code class="doc-inline-code">boost:sync</code> - pulls each active or paused campaign's status from Meta, refreshes its analytics, emails and pushes the owner once a campaign passes 75% of its budget, closes out campaigns that have reached their end date, and recovers campaigns left in a pending payment state. The scheduler only invokes it when <code class="doc-inline-code">META_ACCESS_TOKEN</code> is set</li>
            <li><code class="doc-inline-code">boost:expire-pending</code> - expires any campaign stuck in a pending payment state for more than 30 minutes and cancels its payment intent. It runs whether or not Meta is configured, because the on-network channel leaves the same kind of stuck record behind</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A third command, <code class="doc-inline-code">promo:sync</code>, settles the on-network promotions channel on the same 15 minute schedule. It is gated on <code class="doc-inline-code">ADS_ENABLED</code> rather than on any Meta variable, so it stays dormant on an instance that only uses Meta, and you do not need to configure anything here for it.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>If you already have the scheduler running for other Event Schedule features (e.g. Google Calendar sync, ticket releases), no additional cron configuration is needed.</p>
        </div>

        <h3 class="doc-subheading">Queue workers</h3>
        <p class="text-gray-600 dark:text-gray-300">Creating the campaign on Meta and fetching its analytics are queued jobs. With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> they run immediately inside the web request or the scheduled command, so nothing extra is required. If you have switched to a real queue driver, make sure a worker is running, or campaigns will be created inside the app and never reach Meta.</p>
    </section>
</x-docs-page>
