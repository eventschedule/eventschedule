<x-marketing-layout>
    <x-slot name="title">Email Setup Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Email Setup</x-slot>
    <x-slot name="description">Configure email sending for your selfhosted Event Schedule instance. Set up SMTP, Mailgun, Amazon SES, or other mail drivers for ticket confirmations, newsletters, and notifications.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Email Setup Documentation - Event Schedule",
        "description": "Configure email sending for your selfhosted Event Schedule instance. Set up SMTP, Mailgun, Amazon SES, or other mail drivers for ticket confirmations, newsletters, and notifications.",
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
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Email Setup" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Email Setup</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Configure email delivery so your Event Schedule instance can send ticket confirmations, newsletters, follower notifications, and more.
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
                        <a href="#smtp" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">SMTP Setup</a>
                        <a href="#drivers" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Other Mail Drivers</a>
                        <a href="#sender" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Sender Configuration</a>
                        <a href="#testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Testing</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule uses email for several features. Without a working email configuration, these features will not function:</p>

                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket Confirmations</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Automatic emails sent to buyers after purchasing tickets, including ticket details and QR codes.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Newsletters</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Send newsletters to followers of your schedules with upcoming event announcements.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Follower Notifications</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Automatic notifications sent to followers when new events are published on a schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Account Emails</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Password resets, email verification, and other account-related emails.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Important</div>
                                <p>By default, Event Schedule uses the <code class="doc-inline-code">log</code> mail driver which writes emails to your log file instead of sending them. You must configure a real mail driver before going to production.</p>
                            </div>
                        </section>

                        <!-- SMTP Setup -->
                        <section id="smtp" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                SMTP Setup
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">SMTP is the most common way to send email. You can use any SMTP provider such as your hosting provider's mail server, Gmail, Outlook, or a transactional email service.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Environment Variables</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add these to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">MAIL_MAILER</span>=<span class="code-string">smtp</span>
<span class="code-variable">MAIL_HOST</span>=<span class="code-string">smtp.example.com</span>
<span class="code-variable">MAIL_PORT</span>=<span class="code-string">587</span>
<span class="code-variable">MAIL_USERNAME</span>=<span class="code-string">your-email@example.com</span>
<span class="code-variable">MAIL_PASSWORD</span>=<span class="code-string">your-password</span>
<span class="code-variable">MAIL_ENCRYPTION</span>=<span class="code-string">tls</span>
<span class="code-variable">MAIL_FROM_ADDRESS</span>=<span class="code-string">hello@yourdomain.com</span>
<span class="code-variable">MAIL_FROM_NAME</span>=<span class="code-string">"${APP_NAME}"</span></code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Variable Reference</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_MAILER</code></td>
                                            <td>Mail driver to use</td>
                                            <td><code class="doc-inline-code">smtp</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_HOST</code></td>
                                            <td>SMTP server hostname</td>
                                            <td><code class="doc-inline-code">smtp.gmail.com</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_PORT</code></td>
                                            <td>SMTP port number</td>
                                            <td><code class="doc-inline-code">587</code> (TLS) or <code class="doc-inline-code">465</code> (SSL)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_USERNAME</code></td>
                                            <td>SMTP authentication username</td>
                                            <td>Your email address or API username</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_PASSWORD</code></td>
                                            <td>SMTP authentication password</td>
                                            <td>Your password or app-specific password</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_ENCRYPTION</code></td>
                                            <td>Encryption protocol</td>
                                            <td><code class="doc-inline-code">tls</code> or <code class="doc-inline-code">ssl</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_FROM_ADDRESS</code></td>
                                            <td>Default sender email address</td>
                                            <td><code class="doc-inline-code">hello@yourdomain.com</code></td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">MAIL_FROM_NAME</code></td>
                                            <td>Default sender name</td>
                                            <td><code class="doc-inline-code">${APP_NAME}</code> (uses your app name)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular SMTP Providers</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Here are the settings for commonly used SMTP services:</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Gmail / Google Workspace</h4>
                                    <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                                        <p>Host: <code class="doc-inline-code">smtp.gmail.com</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                                        <p>Requires an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">App Password</a> if 2FA is enabled.</p>
                                    </div>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Outlook / Microsoft 365</h4>
                                    <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                                        <p>Host: <code class="doc-inline-code">smtp.office365.com</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                                    </div>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Amazon SES</h4>
                                    <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                                        <p>Host: <code class="doc-inline-code">email-smtp.us-east-1.amazonaws.com</code> (region-specific) | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                                        <p>Use your SES SMTP credentials (not your AWS access keys).</p>
                                    </div>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mailgun</h4>
                                    <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                                        <p>Host: <code class="doc-inline-code">smtp.mailgun.org</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                                        <p>Username is usually <code class="doc-inline-code">postmaster@yourdomain.com</code>.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Other Mail Drivers -->
                        <section id="drivers" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Other Mail Drivers
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">While SMTP works with any email provider, Laravel also supports dedicated drivers for certain services. These drivers use the provider's API directly, which can be faster and more reliable than SMTP.</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th><code class="doc-inline-code">MAIL_MAILER</code> Value</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>SMTP</td>
                                            <td><code class="doc-inline-code">smtp</code></td>
                                            <td>Works with any SMTP server (recommended for most users)</td>
                                        </tr>
                                        <tr>
                                            <td>Mailgun</td>
                                            <td><code class="doc-inline-code">mailgun</code></td>
                                            <td>Requires <code class="doc-inline-code">MAILGUN_DOMAIN</code> and <code class="doc-inline-code">MAILGUN_SECRET</code></td>
                                        </tr>
                                        <tr>
                                            <td>Amazon SES</td>
                                            <td><code class="doc-inline-code">ses</code></td>
                                            <td>Requires AWS credentials configured</td>
                                        </tr>
                                        <tr>
                                            <td>Postmark</td>
                                            <td><code class="doc-inline-code">postmark</code></td>
                                            <td>Requires <code class="doc-inline-code">POSTMARK_TOKEN</code></td>
                                        </tr>
                                        <tr>
                                            <td>Sendmail</td>
                                            <td><code class="doc-inline-code">sendmail</code></td>
                                            <td>Uses the server's local sendmail binary</td>
                                        </tr>
                                        <tr>
                                            <td>Log</td>
                                            <td><code class="doc-inline-code">log</code></td>
                                            <td>Writes emails to log file (default, for development only)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Recommendation</div>
                                <p>For production selfhosted instances, we recommend using SMTP with a transactional email service like Mailgun, Amazon SES, or Postmark. These services are designed for application-generated emails and offer better deliverability than personal email accounts.</p>
                            </div>
                        </section>

                        <!-- Sender Configuration -->
                        <section id="sender" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                Sender Configuration
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The <code class="doc-inline-code">MAIL_FROM_ADDRESS</code> and <code class="doc-inline-code">MAIL_FROM_NAME</code> determine who emails appear to come from.</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">MAIL_FROM_ADDRESS</span>=<span class="code-string">events@yourdomain.com</span>
<span class="code-variable">MAIL_FROM_NAME</span>=<span class="code-string">"My Event Schedule"</span></code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">DNS Records</div>
                                <p>To improve email deliverability and avoid spam filters, set up these DNS records for your sending domain:</p>
                                <ul class="doc-list mt-2">
                                    <li><strong class="text-gray-900 dark:text-white">SPF</strong> - Authorizes your mail server to send on behalf of your domain</li>
                                    <li><strong class="text-gray-900 dark:text-white">DKIM</strong> - Adds a digital signature to verify email authenticity</li>
                                    <li><strong class="text-gray-900 dark:text-white">DMARC</strong> - Tells receiving servers how to handle authentication failures</li>
                                </ul>
                                <p class="mt-2">Your email provider will give you the specific DNS records to add. Check their documentation for setup instructions.</p>
                            </div>
                        </section>

                        <!-- Testing -->
                        <section id="testing" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                                Testing
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">After configuring your email settings, verify that emails are being sent correctly.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Test</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Use Laravel's built-in Artisan command to send a test email:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>php artisan tinker --execute="Mail::raw('Test email from Event Schedule', function(\$m) { \$m->to('your@email.com')->subject('Test'); });"</code></pre>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-6 mt-4">If the email arrives, your configuration is working. If not, check the troubleshooting section below.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Clear Config Cache</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">After changing <code class="doc-inline-code">.env</code> values, clear the config cache:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>php artisan config:clear</code></pre>
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

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Emails not sending</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for error messages</li>
                                        <li>Verify <code class="doc-inline-code">MAIL_MAILER</code> is not set to <code class="doc-inline-code">log</code> (the default)</li>
                                        <li>Run <code class="doc-inline-code">php artisan config:clear</code> after changing <code class="doc-inline-code">.env</code></li>
                                        <li>Make sure the queue worker is running if emails are queued: <code class="doc-inline-code">php artisan queue:work</code></li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connection refused or timeout</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify your server's firewall allows outbound connections on port <code class="doc-inline-code">587</code> (or <code class="doc-inline-code">465</code>)</li>
                                        <li>Some hosting providers block outbound SMTP. Check with your host.</li>
                                        <li>Try using port <code class="doc-inline-code">465</code> with <code class="doc-inline-code">MAIL_ENCRYPTION=ssl</code> if port <code class="doc-inline-code">587</code> is blocked</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Authentication errors</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Double-check your <code class="doc-inline-code">MAIL_USERNAME</code> and <code class="doc-inline-code">MAIL_PASSWORD</code></li>
                                        <li>For Gmail, use an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">App Password</a> instead of your regular password</li>
                                        <li>Make sure special characters in your password are properly quoted in the <code class="doc-inline-code">.env</code> file (wrap in double quotes)</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Emails going to spam</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Set up SPF, DKIM, and DMARC DNS records for your domain</li>
                                        <li>Use a <code class="doc-inline-code">MAIL_FROM_ADDRESS</code> on a domain you own (not a free email provider)</li>
                                        <li>Consider using a dedicated transactional email service (Mailgun, SES, Postmark)</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
