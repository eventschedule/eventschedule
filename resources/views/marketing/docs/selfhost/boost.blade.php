<x-marketing-layout>
    <x-slot name="title">Boost Setup Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Boost Setup</x-slot>
    <x-slot name="description">Learn how to configure Meta/Facebook ads integration for Event Schedule's boost feature, enabling users to promote events through paid social media campaigns.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Boost Setup Documentation - Event Schedule",
        "description": "Learn how to configure Meta/Facebook ads integration for Event Schedule's boost feature, enabling users to promote events through paid social media campaigns.",
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
        "datePublished": "2024-01-01",
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Boost Setup" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Boost Setup</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Configure Meta/Facebook ads integration to let users promote events through paid Facebook and Instagram campaigns.
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
                        <a href="#facebook-app" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create a Facebook App</a>
                        <a href="#ad-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Meta Business & Ad Account</a>
                        <a href="#facebook-page" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Facebook Page</a>
                        <a href="#system-user" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">System User & Access Token</a>
                        <a href="#pixel" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Meta Pixel</a>
                        <a href="#webhooks" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Webhooks</a>
                        <a href="#app-review" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">App Review</a>
                        <a href="#environment" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Environment Variables</a>
                        <a href="#scheduled-command" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Scheduled Command</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Overview
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The boost feature lets users promote events through paid Facebook and Instagram ads via the Meta Marketing API. The platform acts as an intermediary, creating campaigns, ad sets, and ads on behalf of users using a single platform-owned Meta ad account.</p>
                            <p class="text-gray-600 dark:text-gray-300">This guide walks through the complete Facebook/Meta configuration needed to enable the boost feature.</p>
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
                            </ol>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">META_APP_ID</span>=<span class="code-string">your_app_id</span>
<span class="code-variable">META_APP_SECRET</span>=<span class="code-string">your_app_secret</span></code></pre>
                            </div>
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
                                <li>Note the <strong class="text-gray-900 dark:text-white">Ad Account ID</strong> (numeric, without the <code class="doc-inline-code">act_</code> prefix - the code adds that automatically)</li>
                            </ol>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">META_AD_ACCOUNT_ID</span>=<span class="code-string">your_ad_account_id</span></code></pre>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Ads require a Facebook Page as the ad's identity (the "posted by" entity).</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Create a Facebook Page for your platform (or use an existing one)</li>
                                <li>Go to the Page, click <strong class="text-gray-900 dark:text-white">About</strong> or check the URL to find the Page ID</li>
                                <li>In <strong class="text-gray-900 dark:text-white">Business Settings > Accounts > Pages</strong>, add this page to your Business Account</li>
                            </ol>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">META_PAGE_ID</span>=<span class="code-string">your_page_id</span></code></pre>
                            </div>
                        </section>

                        <!-- Step 4: System User & Access Token -->
                        <section id="system-user" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                Step 4: System User & Access Token
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">A System User provides a stable, long-lived token that doesn't expire when a personal account changes.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In <strong class="text-gray-900 dark:text-white">Business Settings > Users > System Users</strong>, click <strong class="text-gray-900 dark:text-white">Add</strong></li>
                                <li>Name it (e.g. "Event Schedule API") and set the role to <strong class="text-gray-900 dark:text-white">Admin</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Add Assets</strong> and assign:
                                    <ul class="doc-list mt-2">
                                        <li>The Ad Account from Step 2 (with full control)</li>
                                        <li>The Facebook Page from Step 3 (with full control)</li>
                                    </ul>
                                </li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Generate New Token</strong>, select the app from Step 1, and grant these permissions:
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
                        </section>

                        <!-- Step 5: Meta Pixel -->
                        <section id="pixel" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                Step 5: Meta Pixel
                            </h2>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In <strong class="text-gray-900 dark:text-white">Events Manager</strong> (<code class="doc-inline-code">business.facebook.com/events_manager</code>), click <strong class="text-gray-900 dark:text-white">Connect Data Sources</strong></li>
                                <li>Select <strong class="text-gray-900 dark:text-white">Web</strong>, name the pixel (e.g. "Event Schedule Pixel")</li>
                                <li>Choose <strong class="text-gray-900 dark:text-white">Conversions API</strong> as the connection method</li>
                                <li>Note the <strong class="text-gray-900 dark:text-white">Pixel ID</strong></li>
                            </ol>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">META_PIXEL_ID</span>=<span class="code-string">your_pixel_id</span></code></pre>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The app uses webhooks to receive real-time updates when campaigns complete or ads get rejected.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In the Facebook App Dashboard, go to <strong class="text-gray-900 dark:text-white">Add Product</strong> and add <strong class="text-gray-900 dark:text-white">Webhooks</strong></li>
                                <li>Select the <strong class="text-gray-900 dark:text-white">Ad Account</strong> object type</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Subscribe</strong> and configure:
                                    <ul class="doc-list mt-2">
                                        <li><strong class="text-gray-900 dark:text-white">Callback URL:</strong> <code class="doc-inline-code">https://yourdomain.com/webhooks/meta</code></li>
                                        <li><strong class="text-gray-900 dark:text-white">Verify Token:</strong> A random string you choose (e.g. generate with <code class="doc-inline-code">openssl rand -hex 32</code>)</li>
                                    </ul>
                                </li>
                                <li>Subscribe to these fields:
                                    <ul class="doc-list mt-2">
                                        <li><code class="doc-inline-code">campaign</code> - notifies when campaigns complete</li>
                                        <li><code class="doc-inline-code">ad</code> - notifies when ads are approved or rejected</li>
                                        <li><code class="doc-inline-code">ad_account</code> - notifies of account-level changes</li>
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
                                <p>Your server must be publicly accessible at the callback URL for webhook verification to succeed. The verify endpoint is <code class="doc-inline-code">GET /webhooks/meta</code> and the handler is <code class="doc-inline-code">POST /webhooks/meta</code>.</p>
                            </div>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">For production use, your app needs to go through Meta's App Review process.</p>

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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Here is the full set of environment variables for the boost feature:</p>

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

<span class="code-comment"># Required - Pixel for Conversions API</span>
<span class="code-variable">META_PIXEL_ID</span>=<span class="code-string">your_pixel_id</span>

<span class="code-comment"># Required - Webhook verification</span>
<span class="code-variable">META_WEBHOOK_VERIFY_TOKEN</span>=<span class="code-string">your_random_verify_token</span>

<span class="code-comment"># Optional - API version (default: v21.0)</span>
<span class="code-variable">META_API_VERSION</span>=<span class="code-string">v21.0</span>

<span class="code-comment"># Optional - Business settings</span>
<span class="code-variable">META_MARKUP_RATE</span>=<span class="code-value">0.20</span>          <span class="code-comment"># 20% markup on ad spend (default)</span>
<span class="code-variable">META_MIN_BUDGET</span>=<span class="code-value">10.00</span>          <span class="code-comment"># Minimum boost budget (default)</span>
<span class="code-variable">META_MAX_BUDGET</span>=<span class="code-value">1000.00</span>        <span class="code-comment"># Maximum boost budget (default)</span>
<span class="code-variable">META_DEFAULT_CURRENCY</span>=<span class="code-string">USD</span>      <span class="code-comment"># Default currency (default)</span>
<span class="code-variable">META_MAX_CONCURRENT_BOOSTS</span>=<span class="code-value">3</span>   <span class="code-comment"># Max active boosts per schedule (default)</span>
</code></pre>
                            </div>

                            <div class="overflow-x-auto mt-6">
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
                                            <td>Facebook App ID from Step 1</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_APP_SECRET</code></td>
                                            <td>Yes</td>
                                            <td>Facebook App Secret from Step 1</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_ACCESS_TOKEN</code></td>
                                            <td>Yes</td>
                                            <td>System User access token from Step 4</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_AD_ACCOUNT_ID</code></td>
                                            <td>Yes</td>
                                            <td>Numeric Ad Account ID (without <code class="doc-inline-code">act_</code> prefix)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_PAGE_ID</code></td>
                                            <td>Yes</td>
                                            <td>Facebook Page ID for ad identity</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_PIXEL_ID</code></td>
                                            <td>Yes</td>
                                            <td>Meta Pixel ID for Conversions API</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_WEBHOOK_VERIFY_TOKEN</code></td>
                                            <td>Yes</td>
                                            <td>Random string for webhook verification</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_MARKUP_RATE</code></td>
                                            <td>No</td>
                                            <td>Platform markup on ad spend (default: 0.20)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_MIN_BUDGET</code></td>
                                            <td>No</td>
                                            <td>Minimum boost budget in currency units (default: 10.00)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_MAX_BUDGET</code></td>
                                            <td>No</td>
                                            <td>Maximum boost budget in currency units (default: 1000.00)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_API_VERSION</code></td>
                                            <td>No</td>
                                            <td>Meta Graph API version (default: v21.0)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_DEFAULT_CURRENCY</code></td>
                                            <td>No</td>
                                            <td>Default currency code (default: USD)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">META_MAX_CONCURRENT_BOOSTS</code></td>
                                            <td>No</td>
                                            <td>Maximum active boost campaigns per schedule (default: 3)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Config File</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Ensure the <code class="doc-inline-code">page_id</code> key is present in the meta config array in <code class="doc-inline-code">config/services.php</code>:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>config/services.php</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-string">'page_id'</span> => <span class="code-keyword">env</span>(<span class="code-string">'META_PAGE_ID'</span>),</code></pre>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The <code class="doc-inline-code">boost:sync</code> command syncs analytics every 15 minutes (already scheduled in <code class="doc-inline-code">routes/console.php</code>). Make sure the Laravel scheduler is running:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>crontab</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>* * * * * php artisan schedule:run</code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">Note</div>
                                <p>If you already have the scheduler running for other Event Schedule features (e.g. Google Calendar sync, ticket releases), no additional cron configuration is needed.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
