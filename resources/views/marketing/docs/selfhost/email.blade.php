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
                            <h2 class="doc-heading">Overview</h2>
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
                            <h2 class="doc-heading">SMTP Setup</h2>
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
                            <h2 class="doc-heading">Other Mail Drivers</h2>
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
                            <h2 class="doc-heading">Sender Configuration</h2>
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
                            <h2 class="doc-heading">Testing</h2>
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
                            <h2 class="doc-heading">Troubleshooting</h2>

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
