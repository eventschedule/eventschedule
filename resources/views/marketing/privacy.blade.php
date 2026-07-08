<x-marketing-layout>
    <x-slot name="title">Privacy Policy - Event Schedule</x-slot>
    <x-slot name="description">Privacy Policy for Event Schedule - how we collect, use, and protect your data.</x-slot>
    <x-slot name="breadcrumbTitle">Privacy Policy</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Privacy Policy - Event Schedule",
        "description": "Privacy Policy for Event Schedule - how we collect, use, and protect your data.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Privacy Policy"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-legal {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-legal {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- Header -->
    <section class="es-hero relative overflow-hidden bg-white py-20 dark:bg-[#0a0a0f] noise sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 20% 60%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 80% 30%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2">
                <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Privacy</span>
            </div>
            <h1 class="es-balance es-fade-up es-d-2 text-4xl font-black tracking-tight text-gray-900 dark:text-white sm:text-5xl">Privacy <span class="text-gradient-legal">Policy</span></h1>
            <p class="es-fade-up es-d-3 mt-4 text-lg text-gray-600 dark:text-gray-400">Event Schedule LLC</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16 bg-white dark:bg-[#0a0a0f]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg dark:prose-invert max-w-none">

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Privacy Policy, Consent to Process</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    This DPA (Data Privacy Addendum) applies to EventSchedule.com and all associated subdomains owned and operated by Event Schedule. PII (Personally Identifiable Information) is collected directly through account registration. This policy describes your options in deleting/purging your data permanently from Event Schedule in compliance with GDPR.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Security Procedures & Encryption</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We implement technical safeguards to protect your data from unauthorized access. All data transmitted between our systems and users is encrypted using industry-standard encryption protocols (HTTPS/TLS). Sensitive information is also encrypted at rest. Access to user data is restricted through authentication mechanisms and role-based access controls.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Data obtained through Google APIs is handled in accordance with Google's policies and is never sold or shared with third parties. Such data is retained only as long as necessary for service provision and is deleted upon revocation of access.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Use of Google Calendar Data</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Users must explicitly authorize access to their Google Calendar data through Google's OAuth authorization process. We access Google Calendar data solely to provide and improve the core functionality of our services, including:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-4 space-y-2">
                    <li>Viewing, creating, updating, or deleting calendar events as requested by the user</li>
                    <li>Synchronizing events between Google Calendar and Event Schedule</li>
                    <li>Sending notifications and reminders related to calendar events</li>
                </ul>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We do not use Google Calendar data for advertising, marketing, or profiling purposes.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Google Calendar Data Storage & Retention</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We retain Google Calendar data only for as long as necessary to provide our services. If you revoke access to your Google Calendar data through your Google Account settings, we will stop accessing your data and delete any stored calendar data within a reasonable timeframe, unless retention is required for legal or security purposes.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Google Calendar Limited Use Compliance</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Our use of Google Calendar data complies with the Google API Services User Data Policy, including the Limited Use requirements. We only access, use, store, and share Google Calendar data as permitted by these policies and only for the features and services explicitly requested by the user.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Consent: PII Data We Collect</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We collect the following personal information:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>Account name and email address</li>
                    <li>Optional company information (name, website, ID, VAT, phone, address, industry)</li>
                    <li>Geolocation based on IP address</li>
                    <li>For paid accounts: billing information including the last four digits of your card, expiration date, and billing address</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Third Party Data Access</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Per GDPR requirements, we disclose the third-party vendors that may access your data to operate our system:
                </p>
                <ul class="list-disc pl-6 text-gray-600 dark:text-gray-300 mb-6 space-y-2">
                    <li>Cloudflare - Content delivery and security</li>
                    <li>Google Apps - Email and productivity services</li>
                    <li>Stripe - Payment processing</li>
                    <li>SendGrid/Twilio - Email delivery</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Restriction/Erasure: Purging PII Data</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    To permanently delete your account and all associated data:
                </p>
                <ol class="list-decimal pl-6 text-gray-600 dark:text-gray-300 mb-4 space-y-2">
                    <li>Log in to your account</li>
                    <li>Click "Profile" from the top right menu</li>
                    <li>Scroll down to find the "Delete Account" option</li>
                    <li>Click "Delete Account" to permanently remove your data</li>
                </ol>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    The above method of data purge is final, total, and irreversible.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Analytics &amp; Cookies</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We use Google Analytics 4 to understand how visitors use the site. Tracking is opt-in: when you first visit, all analytics, advertising, and personalization signals are set to <em>denied</em> via Google Consent Mode v2. Nothing is read or written to your browser until you click "Allow" in the cookie banner. If you click "Decline", or never respond, we do not set any analytics cookies and only cookieless pings are sent.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    We honor the <a href="https://globalprivacycontrol.org/" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">Global Privacy Control</a> signal: if your browser sends GPC, we treat that as a "Decline" automatically and the banner does not appear.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Your choice is stored in your browser's <code>localStorage</code> under the key <code>cookie_consent</code> (values: <code>granted</code> or <code>denied</code>). It is local to your browser; we do not store it on our servers.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    If you accept, Google Analytics sets first-party cookies named <code>_ga</code> and <code>_ga_&lt;measurement-id&gt;</code>, used to distinguish unique visitors and sessions. We also set <code>ads_data_redaction</code> on every page so any beacons that do fire are stripped of advertising identifiers.
                </p>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    You can withdraw consent at any time. Use the "Cookie preferences" button in the next section to reopen the banner and change your choice. Withdrawing consent is as easy as giving it (GDPR Article 7(3)).
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">{{ __('messages.cookie_consent_privacy_heading') }}</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('messages.cookie_consent_privacy_body') }}
                </p>
                @if (config('services.google.analytics'))
                <button type="button" data-cookie-consent-reopen
                        class="text-blue-600 dark:text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded-sm mb-6">
                    {{ __('messages.cookie_consent_manage') }}
                </button>
                @endif

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Links to Other Websites</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Our website may contain links to other sites. Once you leave our site via these links, we have no control over that other website. We are not responsible for the protection and privacy of any information you provide on those sites, and they are not governed by this privacy policy or our terms of service.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Follower &amp; Engagement Data Visibility</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    When you follow a schedule, purchase a ticket from a schedule, or submit content (such as a comment, photo, or video) to an event, the schedule owner can see your name and email address so they can keep you informed and reach out if needed. This data is shared only with the specific schedule owner you interact with; it is never sold or shared with third parties. You can stop following a schedule at any time from your "Following" page.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Newsletter & Removal</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We periodically send newsletters announcing new features to the email address registered with your account. You can request your email address purged from newsletters by contacting <a href="mailto:privacy@eventschedule.com" class="text-blue-600 dark:text-blue-400 hover:underline">privacy@eventschedule.com</a> or clicking the "unsubscribe" link in any newsletter. Note that we may still send legally required notifications to your registered email.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Age of Consent Privacy</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Our Service does not address anyone under the age of 18. We do not knowingly collect personally identifiable information from anyone under the age of 18. If you are a parent or guardian and you are aware that your child has provided us with personal data, please contact us immediately. If we become aware that we have collected personal data from anyone under the age of 18 without verification of parental consent, we will take steps to remove that information.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Changes to This Privacy Policy</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    We may update this privacy policy from time to time to reflect changes in our practices. If we make material changes, we will notify you by email and newsletter to your registered email address. We encourage you to periodically review this page for the latest information on our privacy practices.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Communication & Resolution</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    If you have any questions about your privacy, data usage, or how to purge your data, please contact us at <a href="mailto:privacy@eventschedule.com" class="text-blue-600 dark:text-blue-400 hover:underline">privacy@eventschedule.com</a>
                </p>
            </div>
        </div>
    </section>
</x-marketing-layout>
