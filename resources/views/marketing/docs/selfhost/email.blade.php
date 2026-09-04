<x-docs-page
    key="selfhost/email"
    description="Configure email sending for selfhosted Event Schedule. Set up SMTP, Amazon SES, sendmail or another mail driver for ticket confirmations and newsletters."
    lede="Configure email delivery so your Event Schedule instance can send ticket confirmations, newsletters, account emails and owner notifications."
    article-description="Configure email sending for your selfhosted Event Schedule instance. Set up SMTP, Amazon SES or another mail driver for ticket confirmations, newsletters and notifications."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#smtp">SMTP Setup</x-doc-nav-link>
        <x-doc-nav-link href="#drivers">Other Mail Drivers</x-doc-nav-link>
        <x-doc-nav-link href="#sender">Sender Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#testing">Testing</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Mail is configured once for the whole install, in your <code class="doc-inline-code">.env</code> file. Every schedule on the instance sends through that one mail transport. Without a working mail configuration these features do nothing:</p>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket and booking confirmations</h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">The confirmation a buyer receives after checkout, with the ticket details and QR code, plus appointment confirmations, reminders and reschedule notices, and gift card deliveries.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Newsletters</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Newsletters a schedule owner writes and sends, plus the automatic new-event digest that goes to confirmed email subscribers when a schedule publishes events. Both leave through this transport, so an install with no mail settings sends neither.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Guest notifications</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Waitlist openings when a spot frees up, post-event feedback requests, and carpool messages between attendees.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Account and owner emails</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Password resets, email verification for accounts and for a schedule's contact address, team member invitations, the daily digest of pending booking requests, opt-in alerts when a ticket sells, and the scheduled event graphic emails an owner sends to their own recipient list.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The default is not a real mail transport</div>
            <p>Out of the box <code class="doc-inline-code">MAIL_MAILER=log</code>. Event Schedule treats <code class="doc-inline-code">log</code> and <code class="doc-inline-code">array</code> as "no mail transport", so ticket and pass confirmations, appointment emails, gift card emails, sale alerts, feedback requests, carpool messages and poll suggestion notices are <strong class="text-gray-900 dark:text-white">skipped entirely</strong> rather than delivered. Mail that is not gated this way, such as password resets, verification emails, team invitations, waitlist openings and newsletters, is written into <code class="doc-inline-code">storage/logs/laravel.log</code> instead of being sent. Configure a real driver before you take a single booking.</p>
        </div>

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Install-wide, and never plan-gated</div>
            <p>A selfhosted install resolves to the Enterprise feature set, so no email feature here is held back by a plan. Note the difference from the hosted service: the per-schedule <strong class="text-gray-900 dark:text-white">Email Settings</strong> tab, found in a schedule's Settings under Integrations, is only rendered when the app runs in hosted mode, and the monthly newsletter allowance, which on the hosted service counts individual recipients rather than newsletters, does not apply to a selfhosted install at all. Your schedules send unlimited newsletters to unlimited recipients through the mail transport you configure below.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">SMTP is the recommended setup for a selfhosted install: it works with every provider and needs no extra packages. You can point it at your hosting provider's mail server, a mailbox at Gmail or Microsoft 365, or a transactional email service such as Amazon SES, Mailgun or Postmark.</p>

        <h3 class="doc-subheading">Configure it in four steps</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Get SMTP credentials from your provider.</strong> A host name, a port, a username and a password. Transactional services issue SMTP credentials that are separate from their API keys.</li>
            <li><strong class="text-gray-900 dark:text-white">Add the variables to <code class="doc-inline-code">.env</code>.</strong> Use the block below as a starting point and replace every value.</li>
            <li><strong class="text-gray-900 dark:text-white">Clear the config cache.</strong> Run <code class="doc-inline-code">php artisan config:clear</code>, otherwise the old values keep being used.</li>
            <li><strong class="text-gray-900 dark:text-white">Send yourself a test message.</strong> See <a href="#testing" class="doc-link">Testing</a> below.</li>
        </ol>

        <h3 class="doc-subheading">Environment Variables</h3>
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

        <h3 class="doc-subheading">Variable Reference</h3>
        <div class="doc-table-wrap">
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
                    <tr>
                        <td><code class="doc-inline-code">MAIL_URL</code></td>
                        <td>Optional. A full SMTP connection string that stands in for the host, port, username and password values above. Useful when a provider hands you a single DSN</td>
                        <td><code class="doc-inline-code">smtp://user:pass@smtp.example.com:587</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">MAIL_EHLO_DOMAIN</code></td>
                        <td>Optional. The domain announced in the SMTP handshake. Defaults to the host in your <code class="doc-inline-code">APP_URL</code>, which is usually correct; set it only if your provider requires a specific EHLO name</td>
                        <td><code class="doc-inline-code">yourdomain.com</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Popular SMTP Providers</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Here are the settings for commonly used SMTP services:</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Gmail / Google Workspace</h4>
                <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                    <p>Host: <code class="doc-inline-code">smtp.gmail.com</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                    <p>Requires an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" class="doc-link">App Password</a> if 2FA is enabled. Personal mailboxes also apply a daily sending cap, so this suits a small instance rather than a busy one.</p>
                </div>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Outlook / Microsoft 365</h4>
                <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                    <p>Host: <code class="doc-inline-code">smtp.office365.com</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                    <p>SMTP authentication has to be enabled for the mailbox in the Microsoft 365 admin center; many tenants have it off by default.</p>
                </div>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Amazon SES</h4>
                <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                    <p>Host: <code class="doc-inline-code">email-smtp.us-east-1.amazonaws.com</code> (region-specific) | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                    <p>Use your SES SMTP credentials (not your AWS access keys). SES also has a dedicated driver, see <a href="#drivers" class="doc-link">Other Mail Drivers</a>.</p>
                </div>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Mailgun</h4>
                <div class="text-gray-600 dark:text-gray-400 text-sm space-y-1">
                    <p>Host: <code class="doc-inline-code">smtp.mailgun.org</code> | Port: <code class="doc-inline-code">587</code> | Encryption: <code class="doc-inline-code">tls</code></p>
                    <p>Username is usually <code class="doc-inline-code">postmaster@yourdomain.com</code>. Mailgun is supported through SMTP; there is no <code class="doc-inline-code">mailgun</code> API driver in Event Schedule.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Besides SMTP, Event Schedule ships with the mailers listed below. Set the one you want as <code class="doc-inline-code">MAIL_MAILER</code>. Anything not in this table has to be added to <code class="doc-inline-code">config/mail.php</code> yourself, and the app will fail with "Mailer is not defined" until it is.</p>

        <div class="doc-table-wrap">
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
                        <td>Works with any SMTP server. Recommended for most installs</td>
                    </tr>
                    <tr>
                        <td>Amazon SES</td>
                        <td><code class="doc-inline-code">ses</code></td>
                        <td>Ready to use. Set <code class="doc-inline-code">AWS_ACCESS_KEY_ID</code>, <code class="doc-inline-code">AWS_SECRET_ACCESS_KEY</code> and <code class="doc-inline-code">AWS_DEFAULT_REGION</code>; the AWS SDK is already bundled</td>
                    </tr>
                    <tr>
                        <td>Sendmail</td>
                        <td><code class="doc-inline-code">sendmail</code></td>
                        <td>Uses the server's local sendmail binary. Override the path with <code class="doc-inline-code">MAIL_SENDMAIL_PATH</code></td>
                    </tr>
                    <tr>
                        <td>Failover</td>
                        <td><code class="doc-inline-code">failover</code></td>
                        <td>Tries <code class="doc-inline-code">smtp</code> first and falls back to <code class="doc-inline-code">log</code>, so a delivery outage is recorded instead of throwing</td>
                    </tr>
                    <tr>
                        <td>Round robin</td>
                        <td><code class="doc-inline-code">roundrobin</code></td>
                        <td>Alternates between <code class="doc-inline-code">ses</code> and <code class="doc-inline-code">postmark</code>. Only worth using once both of those are set up</td>
                    </tr>
                    <tr>
                        <td>Postmark</td>
                        <td><code class="doc-inline-code">postmark</code></td>
                        <td>Set <code class="doc-inline-code">POSTMARK_TOKEN</code>, and install the transport with <code class="doc-inline-code">composer require symfony/postmark-mailer</code>. Postmark's SMTP endpoint needs no extra package</td>
                    </tr>
                    <tr>
                        <td>Resend</td>
                        <td><code class="doc-inline-code">resend</code></td>
                        <td>Set <code class="doc-inline-code">RESEND_KEY</code>, and install the transport with <code class="doc-inline-code">composer require resend/resend-php</code></td>
                    </tr>
                    <tr>
                        <td>Log</td>
                        <td><code class="doc-inline-code">log</code></td>
                        <td>The default. Writes to <code class="doc-inline-code">storage/logs/laravel.log</code> and suppresses transactional email. Development only</td>
                    </tr>
                    <tr>
                        <td>Array</td>
                        <td><code class="doc-inline-code">array</code></td>
                        <td>Keeps messages in memory and sends nothing. Used by the test suite</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Mailgun</div>
            <p>There is no <code class="doc-inline-code">mailgun</code> mailer in <code class="doc-inline-code">config/mail.php</code>, so <code class="doc-inline-code">MAIL_MAILER=mailgun</code> will not boot, and <code class="doc-inline-code">MAILGUN_DOMAIN</code> and <code class="doc-inline-code">MAILGUN_SECRET</code> are not read anywhere. Use Mailgun through <a href="#smtp" class="doc-link">SMTP</a> instead, which is the same infrastructure and needs no extra package.</p>
        </div>

        <div class="doc-callout doc-callout-tip mt-6">
            <div class="doc-callout-title">Recommendation</div>
            <p>For production selfhosted instances, we recommend SMTP pointed at a transactional email service such as Mailgun, Amazon SES or Postmark. These services are built for application-generated email, they let you authenticate your sending domain, and they give you far better deliverability than a personal mailbox.</p>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">The <code class="doc-inline-code">MAIL_FROM_ADDRESS</code> and <code class="doc-inline-code">MAIL_FROM_NAME</code> determine who emails appear to come from. A fresh install ships with <code class="doc-inline-code">MAIL_FROM_ADDRESS="hello@example.com"</code>, a reserved example domain that no receiving server will trust, so change it to an address on a domain you control before you send anything.</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">MAIL_FROM_ADDRESS</span>=<span class="code-string">events@yourdomain.com</span>
<span class="code-variable">MAIL_FROM_NAME</span>=<span class="code-string">"My Event Schedule"</span></code></pre>
        </div>

        <h3 class="doc-subheading">One sender for every schedule</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On a selfhosted install this is the From identity for all outgoing mail, whichever schedule triggered it. There is no per-schedule sender to configure: the Email Settings tab that lets an owner supply their own SMTP credentials is part of the hosted service and is not rendered when the app runs selfhosted. Pick an address that reads sensibly for every schedule on the instance, and one you can actually receive replies at.</p>

        <div class="doc-callout doc-callout-info">
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">After configuring your email settings, verify that mail is really being sent. The Send Test Email button in the admin portal belongs to the hosted per-schedule email settings, so on a selfhosted install you test from the command line.</p>

        <h3 class="doc-subheading">1. Clear the config cache</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Laravel caches <code class="doc-inline-code">.env</code> values, so do this first or you will be testing the old configuration:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan config:clear</code></pre>
        </div>

        <h3 class="doc-subheading">2. Send a test message</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use Laravel's built-in Artisan command to send a test email:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan tinker --execute="Mail::raw('Test email from Event Schedule', function(\$m) { \$m->to('your@email.com')->subject('Test'); });"</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6 mt-4">Send it to a real mailbox you can open. Addresses on the reserved test domains (<code class="doc-inline-code">example.com</code>, <code class="doc-inline-code">example.org</code>, <code class="doc-inline-code">example.net</code>, <code class="doc-inline-code">test.com</code>, <code class="doc-inline-code">test.org</code>, <code class="doc-inline-code">test.net</code>) and anything at <code class="doc-inline-code">@localhost</code> are deliberately never emailed by the app's own notifications.</p>

        <h3 class="doc-subheading">3. Confirm queued mail is being processed</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Ticket confirmations, sale alerts and newsletter batches are dispatched as background jobs rather than sent inline. Which means:</p>
        <ul class="doc-list mb-6">
            <li>With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> they run immediately, in the same request. Nothing extra is needed.</li>
            <li>With <code class="doc-inline-code">database</code> or <code class="doc-inline-code">redis</code> they wait for a worker. Event Schedule's scheduler runs <code class="doc-inline-code">queue:work --stop-when-empty</code> every minute and retries failed jobs every five minutes, so the <code class="doc-inline-code">schedule:run</code> cron job from the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">installation guide</a> is what actually drains the mail queue. No cron, no email.</li>
            <li>Scheduled newsletters are also released by that same cron, once a minute.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300">A real end-to-end check is a free RSVP or a test ticket purchase on one of your own schedules: it exercises the queue, the mailable and the sender address together.</p>
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

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Emails not sending</h3>
                <ul class="doc-list text-sm">
                    <li>Verify <code class="doc-inline-code">MAIL_MAILER</code> is not still <code class="doc-inline-code">log</code> or <code class="doc-inline-code">array</code> (<code class="doc-inline-code">log</code> is the default)</li>
                    <li>Run <code class="doc-inline-code">php artisan config:clear</code> after changing <code class="doc-inline-code">.env</code></li>
                    <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for error messages</li>
                    <li>If <code class="doc-inline-code">QUEUE_CONNECTION</code> is not <code class="doc-inline-code">sync</code>, confirm the <code class="doc-inline-code">schedule:run</code> cron job is installed. It is what runs the queue worker; check <code class="doc-inline-code">storage/logs/scheduler.log</code> and the <code class="doc-inline-code">failed_jobs</code> table</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Nothing arrives and nothing is logged</h4>
                <ul class="doc-list text-sm">
                    <li>With <code class="doc-inline-code">MAIL_MAILER=log</code> the app treats itself as having no mail transport and skips ticket, appointment, gift card, sale-alert and feedback emails outright, so there is no error to find. Configure a real driver</li>
                    <li>Confirmation emails are also skipped for addresses on the reserved test domains listed under <a href="#testing" class="doc-link">Testing</a>. Buy a test ticket with a real address</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Mailer [mailgun] is not defined"</h4>
                <ul class="doc-list text-sm">
                    <li><code class="doc-inline-code">MAIL_MAILER</code> names a mailer that does not exist in <code class="doc-inline-code">config/mail.php</code>. <code class="doc-inline-code">mailgun</code> is the usual culprit; use <code class="doc-inline-code">smtp</code> instead</li>
                    <li>A "class not found" error from a valid value such as <code class="doc-inline-code">postmark</code> or <code class="doc-inline-code">resend</code> means the transport package is missing. See <a href="#drivers" class="doc-link">Other Mail Drivers</a></li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connection refused or timeout</h4>
                <ul class="doc-list text-sm">
                    <li>Verify your server's firewall allows outbound connections on port <code class="doc-inline-code">587</code> (or <code class="doc-inline-code">465</code>)</li>
                    <li>Some hosting providers block outbound SMTP. Check with your host.</li>
                    <li>Try using port <code class="doc-inline-code">465</code> with <code class="doc-inline-code">MAIL_ENCRYPTION=ssl</code> if port <code class="doc-inline-code">587</code> is blocked</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Authentication errors</h4>
                <ul class="doc-list text-sm">
                    <li>Double-check your <code class="doc-inline-code">MAIL_USERNAME</code> and <code class="doc-inline-code">MAIL_PASSWORD</code></li>
                    <li>For Gmail, use an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener noreferrer" class="doc-link">App Password</a> instead of your regular password</li>
                    <li>Make sure special characters in your password are properly quoted in the <code class="doc-inline-code">.env</code> file (wrap in double quotes)</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Emails going to spam</h4>
                <ul class="doc-list text-sm">
                    <li>Set up SPF, DKIM, and DMARC DNS records for your domain</li>
                    <li>Use a <code class="doc-inline-code">MAIL_FROM_ADDRESS</code> on a domain you own, not a free email provider and not the shipped <code class="doc-inline-code">hello@example.com</code></li>
                    <li>Consider using a dedicated transactional email service (Mailgun, SES, Postmark)</li>
                </ul>
            </div>
        </div>
    </section>
</x-docs-page>
