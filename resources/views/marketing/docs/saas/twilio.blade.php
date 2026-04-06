<x-marketing-layout>
    <x-slot name="title">Twilio Integration for SaaS - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Twilio Integration</x-slot>
    <x-slot name="description">Set up Twilio for phone number verification and WhatsApp messaging in your SaaS Event Schedule deployment.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Twilio Integration for SaaS - Event Schedule",
        "description": "Set up Twilio for phone number verification and WhatsApp messaging in your SaaS Event Schedule deployment.",
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
        "datePublished": "2026-02-27",
        "dateModified": "2026-02-27"
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
            <x-docs-breadcrumb currentTitle="Twilio Integration" section="saas" sectionTitle="SaaS" sectionRoute="marketing.docs" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Twilio Integration</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up Twilio to enable phone number verification and WhatsApp messaging for your Event Schedule deployment.
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
                        <a href="#create-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create a Twilio Account</a>
                        <a href="#environment" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Environment Setup</a>
                        <a href="#phone-verification" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Phone Verification</a>
                        <a href="#whatsapp" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">WhatsApp Setup</a>
                        <a href="#testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Testing</a>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Twilio is an optional integration that enables two features in Event Schedule:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Feature</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong class="text-gray-900 dark:text-white">Phone Verification</strong></td>
                                            <td>Users and schedules can verify their phone numbers via SMS code, adding trust and enabling phone-based contact</td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-gray-900 dark:text-white">WhatsApp Messaging</strong></td>
                                            <td>Send event notifications and updates to attendees via WhatsApp, with support for the 24-hour messaging window</td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-gray-900 dark:text-white">WhatsApp Event Creation</strong></td>
                                            <td>Organizers with verified phone numbers can create events by sending a WhatsApp message with event details or a flyer image. AI parses the content and creates the event automatically (Enterprise feature).</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Twilio is entirely optional. If not configured, the app will gracefully skip SMS and WhatsApp features without errors.</p>
                            </div>
                        </section>

                        <!-- Create a Twilio Account -->
                        <section id="create-account" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                Create a Twilio Account
                            </h2>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Sign up for a Twilio account at <code class="doc-inline-code">twilio.com</code></li>
                                <li>From the Twilio Console dashboard, note your <strong class="text-gray-900 dark:text-white">Account SID</strong> and <strong class="text-gray-900 dark:text-white">Auth Token</strong></li>
                                <li>Navigate to <strong class="text-gray-900 dark:text-white">Phone Numbers</strong> &gt; <strong class="text-gray-900 dark:text-white">Manage</strong> &gt; <strong class="text-gray-900 dark:text-white">Buy a number</strong></li>
                                <li>Purchase a phone number with <strong class="text-gray-900 dark:text-white">SMS</strong> capability (and <strong class="text-gray-900 dark:text-white">WhatsApp</strong> if you plan to use WhatsApp messaging)</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Twilio provides trial credit for new accounts, which is sufficient for testing. You can upgrade to a paid account when you are ready to go live.</p>
                            </div>
                        </section>

                        <!-- Environment Setup -->
                        <section id="environment" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Environment Setup
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <pre class="doc-code-block"><code>TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890</code></pre>

                            <div class="space-y-4 mb-6 mt-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_SID</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your Twilio Account SID. Find it on the <strong>Twilio Console</strong> dashboard, displayed prominently at the top of the page.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_AUTH_TOKEN</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your Twilio Auth Token. Found on the same Console dashboard page. Click to reveal the token and copy it.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_FROM_NUMBER</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">The Twilio phone number to send SMS from, in E.164 format (e.g., <code class="doc-inline-code">+15551234567</code>). This must be a number you have purchased or verified in your Twilio account.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Phone Number Verification -->
                        <section id="phone-verification" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                Phone Number Verification
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Once Twilio is configured, phone verification is automatically enabled in two places:</p>

                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">User profiles</strong> - Users can add and verify a phone number on their account. A 6-digit code is sent via SMS and must be entered to confirm ownership.</li>
                                <li><strong class="text-gray-900 dark:text-white">Schedule contact info</strong> - Schedule editors can verify a phone number for their schedule, which is displayed as verified contact information on the public page.</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>The user enters a phone number in E.164 format (e.g., <code class="doc-inline-code">+15551234567</code>)</li>
                                <li>A 6-digit verification code is sent via SMS</li>
                                <li>The code is valid for 10 minutes</li>
                                <li>After successful verification, the phone number is marked as verified</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Rate Limiting</div>
                                <p>To prevent abuse, verification codes are limited to 5 requests per hour per phone number. Failed verification attempts are limited to 5 per 10 minutes.</p>
                            </div>
                        </section>

                        <!-- WhatsApp Setup -->
                        <section id="whatsapp" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                </svg>
                                WhatsApp Setup
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">To send WhatsApp messages, your Twilio number must be registered as a WhatsApp sender.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Register as a WhatsApp Sender</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In the Twilio Console, go to <strong class="text-gray-900 dark:text-white">Messaging</strong> &gt; <strong class="text-gray-900 dark:text-white">Senders</strong> &gt; <strong class="text-gray-900 dark:text-white">WhatsApp Senders</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Add WhatsApp Sender</strong> and follow the guided setup</li>
                                <li>Submit your business profile and messaging templates for Meta approval</li>
                                <li>Once approved, your number can send WhatsApp messages</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-6">Configure the Webhook URL</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Set the incoming message webhook so Event Schedule can receive WhatsApp replies:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In the Twilio Console, go to your WhatsApp Sender settings</li>
                                <li>Set the webhook URL to: <code class="doc-inline-code">https://yourdomain.com/api/whatsapp/webhook</code></li>
                                <li>Set the HTTP method to <strong class="text-gray-900 dark:text-white">POST</strong></li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">24-Hour Messaging Window</div>
                                <p>WhatsApp enforces a 24-hour reply window. You can send free-form messages only within 24 hours of a user's last message. Outside this window, you must use pre-approved message templates. Plan your messaging strategy accordingly.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-6">Event Creation</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When WhatsApp is configured, organizers with verified phone numbers and Enterprise plans can send messages to create events. AI parses both text messages and images (such as flyers) to extract event details automatically.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">For user-facing instructions on creating events via WhatsApp, see the <a href="{{ route('marketing.docs.creating_events') }}#whatsapp" class="text-cyan-400 hover:text-cyan-300">Creating Events guide</a>.</p>
                        </section>

                        <!-- Testing -->
                        <section id="testing" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                                Testing
                            </h2>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Testing SMS</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">During development, you can verify SMS is working by checking the Laravel log file:</p>
                            <pre class="doc-code-block"><code>tail -f storage/logs/laravel.log</code></pre>
                            <p class="text-gray-600 dark:text-gray-300 mb-6 mt-4">If Twilio is not configured, the app will log a warning: <code class="doc-inline-code">Twilio SMS not configured, skipping SMS send</code>. If configured but a send fails, error details including the Twilio response will be logged.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-6">Testing WhatsApp</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Twilio provides a WhatsApp sandbox for testing without requiring Meta approval:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In the Twilio Console, go to <strong class="text-gray-900 dark:text-white">Messaging</strong> &gt; <strong class="text-gray-900 dark:text-white">Try it out</strong> &gt; <strong class="text-gray-900 dark:text-white">Send a WhatsApp message</strong></li>
                                <li>Follow the instructions to join the sandbox by sending a message from your phone to the Twilio sandbox number</li>
                                <li>Once connected, you can send and receive test messages through the sandbox</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>The Twilio sandbox is for development only. For production use, you must complete the WhatsApp sender registration and Meta approval process.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
